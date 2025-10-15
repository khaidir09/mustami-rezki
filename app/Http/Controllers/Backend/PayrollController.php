<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Models\TailorCommission;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Payroll::with('user')->latest('payment_date');

        if (!$user->hasRole('Super Admin')) {
            // Jika ya, tambahkan kondisi where untuk memfilter berdasarkan ID penjahit
            $query->where('user_id', $user->id);
        }

        $payrolls = $query->get();

        return view('admin.backend.payroll.index', compact('payrolls'));
    }

    // Menampilkan halaman form
    public function showGenerateForm()
    {
        $employees = User::role(['Tailor', 'Admin'])->get();
        return view('admin.backend.payroll.generate', compact('employees'));
    }

    // Menghitung dan mengembalikan rincian gaji (dipanggil via AJAX)
    public function calculate(Request $request)
    {
        $user_id = $request->user_id;
        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        // 1. Hitung Gaji Harian (untuk Kasir)
        $daily_salaries = Payroll::where('user_id', $user_id)
            ->where('type', 'Gaji Harian')
            ->where('is_processed', false)
            ->whereBetween('payment_date', [$start_date, $end_date])
            ->get();

        // 2. Hitung Komisi Jahit (untuk Penjahit)
        $tailor_commissions = TailorCommission::where('user_id', $user_id)
            ->whereNull('payroll_id')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->get();

        // 3. Hitung Komisi Produksi Kancing (untuk Kasir)
        $button_commissions = Production::where('user_id', $user_id)
            ->whereNull('payroll_id')
            ->whereBetween('date', [$start_date, $end_date])
            ->get();

        $total_daily_salary = $daily_salaries->sum('amount');
        $total_tailor_commission = $tailor_commissions->sum('amount');
        $total_button_commission = $button_commissions->sum('total_commission');

        $grand_total = $total_daily_salary + $total_tailor_commission + $total_button_commission;

        // Kembalikan data sebagai JSON untuk ditampilkan di view
        return response()->json([
            'total_daily_salary' => $total_daily_salary,
            'total_tailor_commission' => $total_tailor_commission,
            'total_button_commission' => $total_button_commission,
            'grand_total' => $grand_total,
            'details' => [
                'salaries' => $daily_salaries,
                'tailor_commissions' => $tailor_commissions,
                'button_commissions' => $button_commissions,
            ]
        ]);
    }

    // Menyimpan data penggajian mingguan
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'grand_total' => 'required|numeric',
            'final_payment_amount' => 'required|numeric|gte:grand_total' // Pembayaran akhir tidak boleh kurang dari total
        ]);

        $user_id = $request->user_id;
        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        // Ambil total yang dihitung sistem dan total yang akan dibayar
        $calculated_total = (float) $request->grand_total;
        $final_payment = (float) $request->final_payment_amount;

        // Hitung bonus dari selisihnya
        $bonus_amount = $final_payment - $calculated_total;

        DB::beginTransaction();
        try {
            // 1. Buat satu record master di tabel payrolls
            $masterPayroll = Payroll::create([
                'user_id' => $user_id,
                'type' => 'Gaji/Komisi Mingguan',
                'amount' => $final_payment,
                'payment_date' => Carbon::today(),
                'period_start' => $start_date,
                'period_end' => $end_date,
                'description' => 'Pembayaran Gaji & Komisi Mingguan',
                'is_processed' => true, // Payroll mingguan selalu dianggap sudah diproses
            ]);

            if ($bonus_amount > 0) {
                Payroll::create([
                    'user_id' => $user_id,
                    'type' => 'Bonus', // Tipe baru untuk bonus
                    'amount' => $bonus_amount,
                    'payment_date' => Carbon::today(),
                    'period_start' => $start_date,
                    'period_end' => $end_date,
                    'description' => 'Bonus pembulatan dari payroll ID: ' . $masterPayroll->id,
                    'is_processed' => true, // Bonus juga langsung dianggap terproses
                ]);
            }

            // 2. Tandai semua sumber pendapatan sebagai "sudah dibayar"
            Payroll::where('user_id', $user_id)
                ->where('type', 'Gaji Harian')
                ->where('is_processed', false)
                ->whereBetween('payment_date', [$start_date, $end_date])
                ->update(['is_processed' => true, 'description' => 'Telah dibayar pada Payroll ID: ' . $masterPayroll->id]);

            TailorCommission::where('user_id', $user_id)
                ->whereNull('payroll_id')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->update(['payroll_id' => $masterPayroll->id]);

            Production::where('user_id', $user_id)
                ->whereNull('payroll_id')
                ->whereBetween('date', [$start_date, $end_date])
                ->update(['payroll_id' => $masterPayroll->id]);

            DB::commit();

            return redirect()->route('payroll.history')->with('success', 'Pembayaran gaji berhasil diproses!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Payroll $payroll)
    {
        // 1. Ambil rincian gaji harian
        // Kita cari payroll 'daily_salary' yang dibayar oleh payroll mingguan ini.
        $daily_salaries = Payroll::where('type', 'Gaji Harian')
            ->where('user_id', $payroll->user_id)
            ->where('description', 'like', '%Payroll ID: ' . $payroll->id)
            ->get();

        // 2. Ambil rincian komisi jahit
        $tailor_commissions = TailorCommission::with('transaction')
            ->where('payroll_id', $payroll->id)
            ->get();

        // 3. Ambil rincian komisi produksi kancing
        $button_commissions = Production::where('payroll_id', $payroll->id)
            ->get();

        // 4. Ambil data bonus jika ada
        $bonus = Payroll::where('type', 'Bonus')
            ->where('description', 'like', '%payroll ID: ' . $payroll->id)
            ->first();

        return view('admin.backend.payroll.show', compact(
            'payroll',
            'daily_salaries',
            'tailor_commissions',
            'button_commissions',
            'bonus'
        ));
    }

    public function destroy(Payroll $payroll)
    {
        // Pastikan hanya payroll mingguan yang bisa dihapus lewat rute ini
        if ($payroll->type === 'Bonus') {
            return redirect()->route('payroll.history')->with('error', 'Jenis data ini tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $masterPayrollId = $payroll->id;

            // 1. Kembalikan status Gaji Harian menjadi "belum diproses"
            Payroll::where('type', 'Gaji Harian')
                ->where('description', 'like', '%Payroll ID: ' . $masterPayrollId)
                ->update(['is_processed' => false, 'description' => 'Gaji harian dari presensi']);

            // 2. Lepaskan tautan Komisi Jahit
            TailorCommission::where('payroll_id', $masterPayrollId)
                ->update(['payroll_id' => null]);

            // 3. Lepaskan tautan Komisi Produksi Kancing
            Production::where('payroll_id', $masterPayrollId)
                ->update(['payroll_id' => null]);

            // 4. Hapus data Bonus yang terkait
            Payroll::where('type', 'Bonus')
                ->where('description', 'like', '%payroll ID: ' . $masterPayrollId)
                ->delete();

            // 5. Hapus data master payroll itu sendiri
            $payroll->delete();

            DB::commit();

            $notification = [
                'message' => 'Data Payroll berhasil dihapus dan semua rinciannya dikembalikan ke status belum dibayar.',
                'alert-type' => 'success'
            ];
            return redirect()->route('payroll.history')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = [
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
            return redirect()->route('payroll.history')->with($notification);
        }
    }
}

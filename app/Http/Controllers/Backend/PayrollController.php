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

class PayrollController extends Controller
{
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
        $user_id = $request->user_id;
        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        DB::beginTransaction();
        try {
            // 1. Buat satu record master di tabel payrolls
            $masterPayroll = Payroll::create([
                'user_id' => $user_id,
                'type' => 'Gaji/Komisi Mingguan',
                'amount' => $request->grand_total,
                'payment_date' => Carbon::today(),
                'period_start' => $start_date,
                'period_end' => $end_date,
                'description' => 'Pembayaran Gaji & Komisi Mingguan',
                'is_processed' => true, // Payroll mingguan selalu dianggap sudah diproses
            ]);

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
}

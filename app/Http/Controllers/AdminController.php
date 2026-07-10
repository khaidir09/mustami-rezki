<?php

namespace App\Http\Controllers;

use App\Models\Acceptance;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleItem;
use App\Models\Attendance;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Models\FinancialSummary;
use App\Models\TailorCommission;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;
use App\Models\TailorTransactionProduct;
use App\Models\DailyFinancialSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
    // End Method 

    public function AdminProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile', compact('profileData'));
    }
    // End Method 

    public function ProfileStore(Request $request)
    {
        $id = Auth::user()->id;
        $data = User::find($id);

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        $oldPhotoPath = $data->photo;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);
            $data->photo = $filename;

            if ($oldPhotoPath && $oldPhotoPath !== $filename) {
                $this->deleteOldImage($oldPhotoPath);
            }
        }

        $data->save();

        $notification = array(
            'message' => 'Profile Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
    // End Method

    private function deleteOldImage(string $oldPhotoPath): void
    {
        $fullPath = public_path('upload/user_images/' . $oldPhotoPath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    // End private Method

    public function AdminPasswordUpdate(Request $request)
    {

        $user = Auth::user();
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);

        if (!Hash::check($request->old_password, $user->password)) {

            $notification = array(
                'message' => 'Old Password Does not Match!',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }

        User::whereId($user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        Auth::logout();

        $notification = array(
            'message' => 'Password Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('login')->with($notification);
    }
    // End Method

    public function AdminDashboard()
    {
        $user = Auth::user();
        $data = [];

        $today = Carbon::today();

        $data['productCount'] = Product::count();
        $data['lowStockCount'] = Product::where('product_qty', '<=', 3)->count();
        $data['stockValue'] = Product::select(DB::raw('SUM(modal * product_qty) as total_value'))
            ->value('total_value');
        $data['ongoingJobs'] = TailorTransaction::whereIn('status', ['Antrian', 'Dikerjakan'])->count();

        $data['totalProfit'] = ProfitDistribution::whereMonth('realized_at', date('m'))
            ->whereYear('realized_at', date('Y'))
            ->sum('amount');

        $data['completedJobsThisMonth'] = TailorTransaction::whereIn('status', ['Selesai', 'Diambil'])
            ->whereMonth('updated_at', date('m'))
            ->whereYear('updated_at', date('Y'))
            ->count();
        $data['tailorOwnerProfit'] = ProfitDistribution::where('transaction_type', 'App\Models\TailorTransaction')
            ->whereMonth('realized_at', date('m'))
            ->whereYear('realized_at', date('Y'))
            ->sum('amount');

        $data['monthlyExpenses'] = Expense::whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->sum('amount');

        $data['monthlyPurchasesTotal'] = Purchase::whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->sum('grand_total');

        $data['monthlyPayrollTotal'] = Payroll::whereYear('payment_date', date('Y'))
            ->whereMonth('payment_date', date('m'))
            ->where('type', 'Gaji/Komisi Mingguan')
            ->where('is_processed', 1)
            ->sum('amount');

        $data['totalMonthlyExpenditure'] = $data['monthlyExpenses'] + $data['monthlyPurchasesTotal'] + $data['monthlyPayrollTotal'];

        $data['todayExpenses'] = Expense::whereDate('date', $today)
            ->sum('amount');

        // Cek jika user adalah Penjahit, ambil data personal
        if ($user->hasRole('Tailor')) {
            $data['assignedJobs'] = TailorTransaction::where(function ($q) use ($user) {
                $q->where('tailor_id', $user->id)->orWhere('secondary_tailor_id', $user->id);
            })
                ->whereIn('status', ['Antrian', 'Dikerjakan'])
                ->count();

            $data['completedJobsThisMonth'] = TailorTransaction::where(function ($q) use ($user) {
                $q->where('tailor_id', $user->id)->orWhere('secondary_tailor_id', $user->id);
            })
                ->whereIn('status', ['Selesai', 'Diambil'])
                ->whereYear('updated_at', date('Y'))
                ->whereMonth('updated_at', date('m'))
                ->count();

            $data['pendapatanPenjahit'] = TailorCommission::where('user_id', $user->id)
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->sum('amount');
        }

        $data['employees'] = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Admin', 'Tailor']);
        })->get();

        $data['todaysAttendances'] = Attendance::with('user')
            ->where('date', $today)
            ->get()
            ->keyBy('user_id');

        $data['gajiHarian'] = Payroll::where('user_id', $user->id)
            ->where('type', 'Gaji Harian')
            ->whereYear('payment_date', date('Y'))
            ->whereMonth('payment_date', date('m'))
            ->sum('amount');

        $data['jumlahPresensi'] = Payroll::where('user_id', $user->id)
            ->where('type', 'Gaji Harian')
            ->whereYear('payment_date', date('Y'))
            ->whereMonth('payment_date', date('m'))
            ->count();

        $data['komisiProduksi'] = Production::whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->sum('profit');

        $data['jumlahProduksi'] = Production::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('quantity');

        $data['personalProductionCommission'] = Production::where('user_id', $user->id)
            ->whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->sum('total_commission');

        $data['personalProductionCount'] = Production::where('user_id', $user->id)
            ->whereYear('date', date('Y'))
            ->whereMonth('date', date('m'))
            ->sum('quantity');

        $data['profitProduksi'] = Production::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('profit');

        $data['omzetPenjualanLangsungHari'] = Sale::whereDate('date', $today)->sum('grand_total');

        $data['omzetPenjualanDariJahitHari'] = TailorTransactionProduct::whereHas('tailorTransaction', function ($query) {
            $today = Carbon::today();
            $query->whereDate('picked_up_at', $today)->where('status', 'Diambil');
        })->sum('subtotal');

        $data['omzetPenjualanHari'] = $data['omzetPenjualanLangsungHari'] + $data['omzetPenjualanDariJahitHari'];

        $data['omzetJahitHari'] = TailorTransaction::whereDate('picked_up_at', $today)
            ->where('status', 'Diambil')
            ->sum('total_price');

        $data['omzetProduksiHari'] = Production::whereDate('date', $today)
            ->sum('total_price');

        // penerimaan eksternal
        $data['externalIncomeHari'] = Acceptance::whereDate('date', $today)
            ->sum('amount');

        $data['omzetPenjualanLangsung'] = Sale::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('grand_total');

        $data['omzetPenjualanDariJahit'] = TailorTransactionProduct::whereHas('tailorTransaction', function ($query) {
            $query->whereMonth('picked_up_at', date('m'))
                ->whereYear('picked_up_at', date('Y'))
                ->where('status', 'Diambil');
        })->sum('subtotal');

        $data['omzetPenjualan'] = $data['omzetPenjualanLangsung'] + $data['omzetPenjualanDariJahit'];

        $data['omzetJahit'] = TailorTransaction::whereMonth('picked_up_at', date('m'))
            ->whereYear('picked_up_at', date('Y'))
            ->where('status', 'Diambil')
            ->sum('total_price');

        $data['omzetProduksi'] = Production::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('total_price');

        // penerimaan eksternal bulanan
        $data['omzetEksternal'] = Acceptance::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('amount');

        $data['totalSaleItemLangsung'] = SaleItem::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('quantity');

        $data['totalSaleItemDariJahit'] = TailorTransactionProduct::whereHas('tailorTransaction', function ($query) {
            $query->whereMonth('picked_up_at', date('m'))
                ->whereYear('picked_up_at', date('Y'))
                ->where('status', 'Diambil');
        })->sum('quantity');

        $data['totalSaleItem'] = $data['totalSaleItemLangsung'] + $data['totalSaleItemDariJahit'];

        $data['totalTransaksiPenjualanLangsung'] = Sale::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->count();

        $data['totalTransaksiPenjualanDariJahit'] = TailorTransaction::whereMonth('picked_up_at', date('m'))
            ->whereYear('picked_up_at', date('Y'))
            ->where('status', 'Diambil')
            ->whereHas('soldProducts')
            ->count();

        $data['totalTransaksiPenjualan'] = $data['totalTransaksiPenjualanLangsung'] + $data['totalTransaksiPenjualanDariJahit'];

        $data['totalProfitPenjualan'] = ProfitDistribution::whereIn('transaction_type', ['App\Models\Sale', 'App\Models\TailorTransactionProduct'])
            ->whereMonth('realized_at', date('m'))
            ->whereYear('realized_at', date('Y'))
            ->sum('amount');

        $data['externalIncome'] = Acceptance::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('amount');

        $activeSummary = FinancialSummary::where('status', 'Aktif')->first();
        $data['kas'] = $activeSummary->opening_balance;


        $data['totalProfitKotor'] = $data['totalProfit'];

        // --- Perbaikan Perhitungan Uang Bersih (Berdasarkan Arus Kas Harian) ---
        $latestDailySummary = DailyFinancialSummary::orderBy('date', 'desc')->first();

        $openingCash = 0;
        $startDate = null;

        if ($latestDailySummary) {
            // Jika ada laporan harian, ambil saldo akhir sebagai saldo awal periode ini
            $openingCash = $latestDailySummary->closing_balance;
            // Hitung akumulasi mulai dari BESOKNYA laporan terakhir s/d SEKARANG
            $startDate = $latestDailySummary->date->addDay()->startOfDay();
        } else {
            // Fallback ke Laporan Bulanan (jika belum ada laporan harian sama sekali)
            $activeSummary = FinancialSummary::where('status', 'Aktif')->first();
            if ($activeSummary) {
                $openingCash = $activeSummary->opening_balance;
                $startDate = Carbon::create($activeSummary->year, $activeSummary->month, 1)->startOfDay();
            } else {
                // Absolute fallback (misal sistem baru)
                $startDate = Carbon::today()->startOfMonth();
            }
        }

        // Hitung Akumulasi Pemasukan sejak $startDate
        $salesIncome = Sale::where('date', '>=', $startDate)->sum('grand_total');

        $salesIncomeDariJahit = TailorTransactionProduct::whereHas('tailorTransaction', function ($query) use ($startDate) {
            $query->where('status', 'Diambil')->whereDate('picked_up_at', '>=', $startDate);
        })->sum('subtotal');

        $tailorIncome = TailorTransaction::where('picked_up_at', '>=', $startDate)->where('status', 'Diambil')->sum('total_price');
        $productionIncome = Production::where('date', '>=', $startDate)->sum('total_price');
        $externalIncomeNew = Acceptance::where('date', '>=', $startDate)->sum('amount');

        $totalIncomeNew = $salesIncome + $salesIncomeDariJahit + $tailorIncome + $productionIncome + $externalIncomeNew;

        // Hitung Akumulasi Pengeluaran sejak $startDate
        $purchaseExpense = Purchase::where('date', '>=', $startDate)->sum('grand_total');
        $operationalExpense = Expense::where('date', '>=', $startDate)->sum('amount');
        $payrollExpense = Payroll::where('payment_date', '>=', $startDate)
            ->where('type', 'Gaji/Komisi Mingguan')
            ->where('is_processed', 1)
            ->sum('amount');

        $totalExpenseNew = $purchaseExpense + $operationalExpense + $payrollExpense;

        // Uang Bersih = Saldo Awal (dari closing terakhir) + Pemasukan Baru - Pengeluaran Baru
        $data['uangBersih'] = $openingCash + $totalIncomeNew - $totalExpenseNew;


        return view('admin.index', $data);
    }
}

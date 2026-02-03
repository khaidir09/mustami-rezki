<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Purchase;
use App\Models\Acceptance;
use App\Models\Production;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FinancialSummary;
use App\Models\TailorTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DailyFinancialSummary;
use App\Models\TailorTransactionProduct;

class FinancialReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $financials = FinancialSummary::latest()->get();
        return view('admin.backend.financial.index', compact('financials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeSummary = FinancialSummary::where('status', 'Aktif')->first();
        $year = $activeSummary->year;
        $month = $activeSummary->month;
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate Totals from Daily Financial Summaries
        $totalIncome = DailyFinancialSummary::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('total_income');

        $totalExpense = DailyFinancialSummary::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('total_expense');


        // 4. Hitung Saldo Akhir
        $openingBalance = $activeSummary->opening_balance;
        $closingBalance = ($openingBalance + $totalIncome) - $totalExpense;

        return view('admin.backend.financial.create', compact(
            'activeSummary',
            'totalIncome',
            'totalExpense',
            'openingBalance',
            'closingBalance',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');

        DB::beginTransaction();
        try {
            // 1. Ambil data laporan bulan ini yang akan ditutup
            $summaryToClose = FinancialSummary::where('year', $year)
                ->where('month', $month)
                ->where('status', 'Aktif')
                ->firstOrFail(); // Gagal jika tidak ditemukan

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // 2. Hitung Total Pemasukan (Income)
            $salesIncome = Sale::whereBetween('date', [$startDate, $endDate])->sum('grand_total');
            $salesIncomeDariJahit = TailorTransactionProduct::whereHas('tailorTransaction', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'Diambil')
                    ->whereBetween('picked_up_at', [$startDate, $endDate]);
            })->sum('subtotal');
            $salesIncome += $salesIncomeDariJahit;

            $tailorIncome = TailorTransaction::whereBetween('picked_up_at', [$startDate, $endDate])
                ->where('status', 'Diambil')
                ->sum('total_price');
            $productionIncome = Production::whereBetween('date', [$startDate, $endDate])->sum('total_price');
            $externalIncome = Acceptance::whereBetween('date', [$startDate, $endDate])->sum('amount');
            $totalIncome = $salesIncome + $tailorIncome + $productionIncome + $externalIncome;

            // 3. Hitung Total Pengeluaran (Expenditure)
            $purchaseExpense = Purchase::whereBetween('date', [$startDate, $endDate])->sum('grand_total');
            $operationalExpense = Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');
            $payrollExpense = Payroll::whereBetween('payment_date', [$startDate, $endDate])->where('type', 'Gaji/Komisi Mingguan')
                ->where('is_processed', 1)
                ->sum('amount');
            $totalExpense = $purchaseExpense + $operationalExpense + $payrollExpense;

            // 4. Hitung Saldo Akhir
            $openingBalance = $summaryToClose->opening_balance;
            $closingBalance = ($openingBalance + $totalIncome) - $totalExpense;

            // 5. Update laporan bulan ini dan tutup
            $summaryToClose->update([
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'closing_balance' => $closingBalance,
                'status' => 'Tutup',
            ]);

            // 6. Siapkan dan buat laporan untuk bulan berikutnya
            $nextMonthDate = $startDate->addMonth();
            FinancialSummary::create([
                'year' => $nextMonthDate->year,
                'month' => $nextMonthDate->month,
                'opening_balance' => $closingBalance, // Saldo akhir bulan ini adalah saldo awal bulan depan
                'total_income' => 0,
                'total_expense' => 0,
                'closing_balance' => 0, // Awalnya 0
                'status' => 'Aktif', // Bulan berikutnya menjadi aktif
            ]);

            DB::commit();

            $notification = [
                'message' => 'Tutup buku untuk periode ' . $startDate->translatedFormat('F Y') . ' berhasil.',
                'alert-type' => 'success'
            ];
            // Arahkan ke halaman riwayat laporan
            return redirect()->route('financial.index')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }
    }

    public function cetak($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // 1. Ambil Ringkasan Utama
        $summary = FinancialSummary::where('year', $year)->where('month', $month)->firstOrFail();

        // 2. Ambil Rincian Pemasukan
        $sales = Sale::with('saleItems.product')->whereBetween('date', [$startDate, $endDate])->get();
        $tailorTransactions = TailorTransaction::with('soldProducts')
            ->whereBetween('picked_up_at', [$startDate, $endDate])
            ->where('status', 'Diambil')
            ->get();
        $productions = Production::whereBetween('date', [$startDate, $endDate])->get();
        $acceptances = Acceptance::whereBetween('date', [$startDate, $endDate])->get();

        // 3. Ambil Rincian Pengeluaran
        $purchases = Purchase::whereBetween('date', [$startDate, $endDate])->get();
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])->get();
        $payrolls = Payroll::whereBetween('payment_date', [$startDate, $endDate])->where('type', 'Gaji/Komisi Mingguan')->where('is_processed', 1)->get();

        // Hitung Total Per Kategori
        $totalSales = $sales->sum('grand_total');
        $totalTailor = $tailorTransactions->sum(function ($tailor) {
            return $tailor->total_price + $tailor->soldProducts->sum('subtotal');
        });
        $totalProduction = $productions->sum('total_price');
        $totalAcceptance = $acceptances->sum('amount');

        $totalPurchase = $purchases->sum('grand_total');
        $totalOperational = $expenses->sum('amount');
        $totalPayroll = $payrolls->sum('amount');

        $pdf = Pdf::loadView('admin.backend.financial.report', compact(
            'summary',
            'sales',
            'tailorTransactions',
            'productions',
            'acceptances',
            'purchases',
            'expenses',
            'payrolls',
            'totalSales',
            'totalTailor',
            'totalProduction',
            'totalAcceptance',
            'totalPurchase',
            'totalOperational',
            'totalPayroll'
        ));
        return $pdf->stream('Laporan Arus Kas' . $month . $year . '.pdf');
    }
}

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
use App\Models\FinancialSummary;
use App\Models\DailyFinancialSummary;
use App\Models\TailorTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TailorTransactionProduct;
use Barryvdh\DomPDF\Facade\Pdf;

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
            'closingBalance'
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

            // Calculate Totals from Daily Financial Summaries
            $totalIncome = DailyFinancialSummary::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('total_income');

            $totalExpense = DailyFinancialSummary::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('total_expense');

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
        $sales = Sale::whereBetween('date', [$startDate, $endDate])->get();
        $tailorTransactions = TailorTransaction::with('soldProducts')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();
        $productions = Production::whereBetween('date', [$startDate, $endDate])->get();
        $acceptances = Acceptance::whereBetween('date', [$startDate, $endDate])->get();

        // 3. Ambil Rincian Pengeluaran
        $purchases = Purchase::whereBetween('date', [$startDate, $endDate])->get();
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])->get();
        $payrolls = Payroll::whereBetween('payment_date', [$startDate, $endDate])->where('type', 'Gaji/Komisi Mingguan')->where('is_processed', 1)->get();

        $pdf = Pdf::loadView('admin.backend.financial.report', compact(
            'summary',
            'sales',
            'tailorTransactions',
            'productions',
            'acceptances',
            'purchases',
            'expenses',
            'payrolls',
        ));
        return $pdf->stream('Laporan Arus Kas' . $month . $year . '.pdf');
    }
}

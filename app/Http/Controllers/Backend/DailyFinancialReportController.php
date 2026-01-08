<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyFinancialSummary;
use App\Models\FinancialSummary;
use App\Models\Sale;
use App\Models\TailorTransaction;
use App\Models\Production;
use App\Models\Acceptance;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\TailorTransactionProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyFinancialReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $summaries = DailyFinancialSummary::orderBy('date', 'desc')->get();
        return view('admin.backend.daily_financial.index', compact('summaries'));
    }

    /**
     * Show the form for creating a new resource (Closing for a specific day, default today).
     */
    public function create(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        // Check if already closed
        $existing = DailyFinancialSummary::whereDate('date', $date)->first();
        if ($existing) {
            $notification = [
                'message' => 'Laporan harian untuk tanggal ' . $date->format('d-m-Y') . ' sudah ditutup.',
                'alert-type' => 'warning'
            ];
            // You might want to allow viewing the report instead, but for now redirect or show warning
            // For now, let's redirect to index with warning
            return redirect()->route('daily.financial.index')->with($notification);
        }

        // Hitung Rincian Pemasukan (Income)
        $salesIncome = Sale::whereDate('date', $date)->sum('grand_total');
        $tailorIncome = TailorTransaction::whereDate('transaction_date', $date)->where('status', 'Diambil')->sum('paid_amount');
        $productionIncome = Production::whereDate('date', $date)->sum('total_price');
        $externalIncome = Acceptance::whereDate('date', $date)->sum('amount');
        $totalIncome = $salesIncome + $tailorIncome + $productionIncome + $externalIncome;

        // Hitung Rincian Pengeluaran (Expenditure)
        $purchaseExpense = Purchase::whereDate('date', $date)->sum('grand_total');
        $operationalExpense = Expense::whereDate('date', $date)->sum('amount');
        $payrollExpense = Payroll::whereDate('payment_date', $date)->where('type', 'Gaji/Komisi Mingguan')->where('is_processed', 1)->sum('amount');
        $totalExpense = $purchaseExpense + $operationalExpense + $payrollExpense;

        // --- Calculate Opening Balance ---
        $openingBalance = 0;

        // 1. Try to get yesterday's closing balance
        $yesterday = $date->copy()->subDay();
        $previousSummary = DailyFinancialSummary::whereDate('date', $yesterday)->first();

        if ($previousSummary) {
            $openingBalance = $previousSummary->closing_balance;
        } else {
            // 2. If no daily summary for yesterday, try to calculate from Monthly Summary
            // Logic: Monthly Opening + Income(MonthStart -> Yesterday) - Expense(MonthStart -> Yesterday)

            // Get active or closed monthly summary for this month
            // Note: If we are in October, we look for October Summary.
            $monthlySummary = FinancialSummary::where('year', $date->year)
                ->where('month', $date->month)
                ->first();

            if ($monthlySummary) {
                $monthlyOpening = $monthlySummary->opening_balance;

                $monthStart = $date->copy()->startOfMonth();
                // Range from MonthStart to Yesterday (End of Day)
                $calcStart = $monthStart;
                $calcEnd = $yesterday->endOfDay();

                // If date is 1st of month, yesterday is prev month.
                // If date is 1st, we just take Monthly Opening.
                if ($date->day == 1) {
                    $openingBalance = $monthlyOpening;
                } else {
                    // Calculate accumulated flows
                    $accIncome = $this->calculateIncome($calcStart, $calcEnd);
                    $accExpense = $this->calculateExpense($calcStart, $calcEnd);

                    $openingBalance = $monthlyOpening + $accIncome - $accExpense;
                }
            } else {
                // Fallback: No monthly summary found? Maybe start with 0.
                $openingBalance = 0;
            }
        }

        // --- Calculate Today's Flow ---
        $todayStart = $date->copy()->startOfDay();
        $todayEnd = $date->copy()->endOfDay();

        $totalIncome = $this->calculateIncome($todayStart, $todayEnd);
        $totalExpense = $this->calculateExpense($todayStart, $todayEnd);

        $closingBalance = $openingBalance + $totalIncome - $totalExpense;

        return view('admin.backend.daily_financial.create', compact(
            'date',
            'openingBalance',
            'totalIncome',
            'totalExpense',
            'closingBalance',
            'salesIncome',
            'tailorIncome',
            'productionIncome',
            'externalIncome',
            'purchaseExpense',
            'operationalExpense',
            'payrollExpense',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'opening_balance' => 'required|numeric',
            'total_income' => 'required|numeric',
            'total_expense' => 'required|numeric',
            'closing_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->date);

        // Double check existence
        if (DailyFinancialSummary::whereDate('date', $date)->exists()) {
            $notification = [
                'message' => 'Laporan harian untuk tanggal ini sudah ada.',
                'alert-type' => 'error'
            ];
            return redirect()->route('daily.financial.index')->with($notification);
        }

        DailyFinancialSummary::create([
            'date' => $date,
            'opening_balance' => $request->opening_balance,
            'total_income' => $request->total_income,
            'total_expense' => $request->total_expense,
            'closing_balance' => $request->closing_balance,
            'notes' => $request->notes,
        ]);

        $notification = [
            'message' => 'Tutup buku harian berhasil.',
            'alert-type' => 'success'
        ];

        return redirect()->route('daily.financial.index')->with($notification);
    }

    private function calculateIncome($startDate, $endDate)
    {
        // 1. Sale
        $salesIncome = Sale::whereBetween('date', [$startDate, $endDate])->sum('grand_total');

        // 2. Tailor Transaction Product (Sales from Tailor)
        $salesIncomeDariJahit = TailorTransactionProduct::whereHas('tailorTransaction', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        })->sum('subtotal');

        $salesIncome += $salesIncomeDariJahit;

        // 3. Tailor Transaction (Service)
        $tailorIncome = TailorTransaction::whereBetween('transaction_date', [$startDate, $endDate])->sum('total_price');

        // 4. Production
        $productionIncome = Production::whereBetween('date', [$startDate, $endDate])->sum('total_price');

        // 5. External Acceptance
        $externalIncome = Acceptance::whereBetween('date', [$startDate, $endDate])->sum('amount');

        return $salesIncome + $tailorIncome + $productionIncome + $externalIncome;
    }

    private function calculateExpense($startDate, $endDate)
    {
        // 1. Purchase
        $purchaseExpense = Purchase::whereBetween('date', [$startDate, $endDate])->sum('grand_total');

        // 2. Operational Expense
        $operationalExpense = Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');

        // 3. Payroll
        $payrollExpense = Payroll::whereBetween('payment_date', [$startDate, $endDate])
            ->where('type', 'Gaji/Komisi Mingguan')
            ->where('is_processed', 1)
            ->sum('amount');

        return $purchaseExpense + $operationalExpense + $payrollExpense;
    }

    public function cetak($id)
    {
        $summary = DailyFinancialSummary::findOrFail($id);
        $date = $summary->date;

        // 1. Ambil Rincian Pemasukan
        $sales = Sale::whereDate('date', $date)->get();
        $tailorTransactions = TailorTransaction::with('soldProducts')
            ->whereDate('transaction_date', $date)
            ->where('status', 'Diambil')
            ->get();
        $productions = Production::whereDate('date', $date)->get();
        $acceptances = Acceptance::whereDate('date', $date)->get();

        // 2. Ambil Rincian Pengeluaran
        $purchases = Purchase::whereDate('date', $date)->get();
        $expenses = Expense::whereDate('date', $date)->get();
        $payrolls = Payroll::whereDate('payment_date', $date)
            ->where('type', 'Gaji/Komisi Mingguan')
            ->where('is_processed', 1)
            ->get();

        // Hitung Total Per Kategori
        $totalSales = $sales->sum('grand_total');
        $totalTailor = $tailorTransactions->sum(function ($tailor) {
            return $tailor->paid_amount + $tailor->soldProducts->sum('subtotal');
        });
        $totalProduction = $productions->sum('total_price');
        $totalAcceptance = $acceptances->sum('amount');

        $totalPurchase = $purchases->sum('grand_total');
        $totalOperational = $expenses->sum('amount');
        $totalPayroll = $payrolls->sum('amount');

        $pdf = Pdf::loadView('admin.backend.daily_financial.report', compact(
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

        return $pdf->stream('Laporan Arus Kas Harian ' . $date->format('d-m-Y') . '.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;

class ProfitDistributionController extends Controller
{
    public function index(Request $request)
    {
        // Set default dates to current month if not provided
        $startDate = $request->input('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->input('end_date') ?: Carbon::now()->endOfMonth()->toDateString();

        // 1. Memulai query builder dari model ProfitDistribution
        $query = ProfitDistribution::query();

        // 2. Terapkan filter berdasarkan rentang tanggal
        $query->whereDate('created_at', '>=', $startDate);
        $query->whereDate('created_at', '<=', $endDate);

        $totalProfit = (clone $query)->sum('amount');

        // Hitung Total Omzet
        $saleQuery = Sale::query();
        $tailorQuery = TailorTransaction::query();
        $productionQuery = Production::query();

        $saleQuery->whereDate('date', '>=', $startDate);
        $tailorQuery->whereDate('transaction_date', '>=', $startDate);
        $productionQuery->whereDate('date', '>=', $startDate);

        $saleQuery->whereDate('date', '<=', $endDate);
        $tailorQuery->whereDate('transaction_date', '<=', $endDate);
        $productionQuery->whereDate('date', '<=', $endDate);

        $totalOmzet = $saleQuery->sum('grand_total') + $tailorQuery->sum('paid_amount') + $productionQuery->sum('total_price');

        // 4. Ambil data detail untuk tabel dengan paginasi, Urutkan berdasarkan yang terbaru
        $distributions = $query->latest()->get();

        // 5. Kirim semua data ke view
        return view('admin.backend.profit.index', compact(
            'distributions',
            'totalProfit',
            'totalOmzet',
            'startDate',
            'endDate'
        ));
    }
}

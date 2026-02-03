<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Production;
use Illuminate\Http\Request;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;
use App\Models\TailorTransactionProduct;

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
        // ambil data produk terjual dari transaksi jahit yang sudah diambil
        $tailorProductQuery = TailorTransactionProduct::whereHas('tailorTransaction', function ($q) use ($request) {
            $startDate = $request->input('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
            $endDate = $request->input('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
            $q->where('status', 'Diambil')->whereBetween('picked_up_at', [$startDate, $endDate]);
        });

        $saleQuery->whereDate('date', '>=', $startDate);
        $tailorQuery->where('status', 'Diambil')->whereDate('picked_up_at', '>=', $startDate);
        $productionQuery->whereDate('date', '>=', $startDate);

        $saleQuery->whereDate('date', '<=', $endDate);
        $tailorQuery->where('status', 'Diambil')->whereDate('picked_up_at', '<=', $endDate);
        $productionQuery->whereDate('date', '<=', $endDate);

        $totalOmzet = $saleQuery->sum('grand_total') + $tailorQuery->sum('total_price') + $productionQuery->sum('total_price') + $tailorProductQuery->sum('subtotal');

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

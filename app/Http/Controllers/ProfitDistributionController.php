<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfitDistribution;

class ProfitDistributionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Memulai query builder dari model ProfitDistribution
        // Menggunakan query() agar kita bisa menambahkan filter secara dinamis
        $query = ProfitDistribution::query();

        // 2. Terapkan filter berdasarkan rentang tanggal jika ada input
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 3. Hitung total untuk kartu ringkasan (summary cards)
        // PENTING: Gunakan clone() agar filter utama tidak terpengaruh oleh where tambahan
        $totalModal = (clone $query)->where('distribution_type', 'pengembangan_modal')->sum('amount');
        $totalPribadi = (clone $query)->where('distribution_type', 'pribadi')->sum('amount');
        $totalSedekah = (clone $query)->where('distribution_type', 'sedekah')->sum('amount');

        // 4. Ambil data detail untuk tabel dengan paginasi
        // Urutkan berdasarkan yang terbaru
        $distributions = $query->latest()->get();

        // 5. Kirim semua data ke view
        return view('admin.backend.profit.index', compact(
            'distributions',
            'totalModal',
            'totalPribadi',
            'totalSedekah'
        ));
    }
}

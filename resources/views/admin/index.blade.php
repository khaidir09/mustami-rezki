@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">
        {{-- Statistik Profit --}}
        <div class="row">
            <div class="col-12">
                <div class="py-3">
                    <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
                </div>
            </div>
        </div>
        @if (Auth::user()->hasRole('Super Admin'))
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Pengembangan Modal (Bulan Ini)</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-success">Rp {{ number_format($totalModal, 0, ',', '.') }}</div>
                        </div>
                        <a href="{{ route('profit.distribution.report', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="text-muted fs-12">
                            Lihat Laporan Lengkap
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Dana Pribadi (Bulan Ini)</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-success">Rp {{ number_format($totalPribadi, 0, ',', '.') }}</div>
                        </div>
                        <a href="{{ route('profit.distribution.report', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="text-muted fs-12">
                            Lihat Laporan Lengkap
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Dana Sedekah (Bulan Ini)</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-success">Rp {{ number_format($totalSedekah, 0, ',', '.') }}</div>
                        </div>
                        <a href="{{ route('profit.distribution.report', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="text-muted fs-12">
                            Lihat Laporan Lengkap
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="fs-14 mb-1">Pengeluaran (Bulan Ini)</div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <div class="fs-22 mb-0 me-2 fw-semibold text-danger">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</div>
                        </div>
                        <a href="{{ route('all.expense', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="text-muted fs-12">
                            Lihat Laporan Lengkap
                        </a>                                        
                    </div>
                </div>
            </div>
            
        </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-2">Informasi Kehadiran Karyawan Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})</h4>

                        <div class="table-responsive">
                            <table class="table table-nowrap table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama Karyawan</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $employee->name }}</td>
                                        <td>
                                            @php
                                                $attendance = $todaysAttendances->get($employee->id);
                                            @endphp

                                            @if($attendance)
                                                @if($attendance->status == 'Hadir')
                                                    <span class="badge badge-soft-success font-size-12">Hadir</span>
                                                @elseif($attendance->status == 'Izin')
                                                    <span class="badge badge-soft-warning font-size-12">Izin</span>
                                                @elseif($attendance->status == 'Sakit')
                                                    <span class="badge badge-soft-danger font-size-12">Sakit</span>
                                                @endif
                                            @else
                                                <span class="badge badge-soft-secondary font-size-12">Belum Ada Info</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance)
                                                @if($attendance->status == 'Hadir')
                                                    Masuk Pukul: <strong>{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</strong>
                                                @else
                                                    {{-- Tampilkan notes jika ada --}}
                                                    {{ $attendance->notes ?? 'Tanpa Keterangan' }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @if (Auth::user()->hasRole('Super Admin'))
            <div class="row">
                <div class="col-12">
                    <div class="pb-3 d-flex align-items-sm-center flex-sm-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-18 fw-semibold m-0">Produksi</h4>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Produksi Bulan Ini</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ $jumlahProduksi }} Item</div>
                                    </div>
                                    <a href="{{ route('all.production') }}" class="text-muted fs-12">
                                        Lihat Produksi
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Profit Produksi Bulan Ini</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">Rp {{ number_format($profitProduksi, 0, ',', '.') }}</div>
                                    </div>
                                    <a href="{{ route('all.production') }}" class="text-muted fs-12">
                                        Lihat Produksi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (Auth::user()->hasRole('Admin'))
            <div class="row">
                <div class="col-12">
                    <div class="pb-3 d-flex align-items-sm-center flex-sm-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-18 fw-semibold m-0">Gaji/Komisi</h4>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Gaji Harian Bulan Ini</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">Rp {{ number_format($gajiHarian, 0, ',', '.') }} ({{ $jumlahPresensi }} Hari)</div>
                                    </div>
                                    <a href="{{ route('attendances.index') }}" class="text-muted fs-12">
                                        Lihat Riwayat Presensi
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Komisi Produksi Bulan Ini</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">Rp {{ number_format($komisiProduksi, 0, ',', '.') }} ({{ $jumlahProduksi }} Item)</div>
                                    </div>
                                    <a href="{{ route('all.production') }}" class="text-muted fs-12">
                                        Lihat Produksi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{-- Statistik Jahit --}}
        @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
        <div class="row">
                <div class="col-12">
                    <div class="pb-3 d-flex align-items-sm-center flex-sm-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-18 fw-semibold m-0">Statistik Jahit</h4>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Jahitan Berjalan</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ $ongoingJobs }} Transaksi</div>
                                    </div>
                                    <a href="{{ route('all.tailor') }}" class="text-muted fs-12">
                                        Lihat Daftar Transaksi
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Jahitan Selesai</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ $completedJobsThisMonth }} Transaksi</div>
                                    </div>
                                    <a href="{{ route('all.tailor') }}" class="text-muted fs-12">
                                        Lihat Daftar Transaksi
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if (Auth::user()->hasRole('Super Admin'))
                            <div class="col-md-6 col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-14 mb-1">Profit Toko</div>
                                        </div>
                                        <div class="d-flex align-items-baseline">
                                            <div class="fs-22 mb-0 me-2 fw-semibold text-success">Rp {{ number_format($tailorOwnerProfit, 0, ',', '.') }}</div>
                                        </div>
                                        <a href="{{ route('profit.distribution.report') }}" class="text-muted fs-12">
                                            Lihat Laporan Profit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div> 
                </div>
            </div
        @endif>
        @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
            {{-- Statistik Produk --}}
            <div class="row">
                <div class="col-12">
                    <div class="pb-3 d-flex align-items-sm-center flex-sm-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-18 fw-semibold m-0">Statistik Produk</h4>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Total Produk</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ $productCount }} Item</div>
                                    </div>
                                    <a href="{{ route('all.product') }}" class="text-muted fs-12">
                                        Lihat Semua Produk
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if (Auth::user()->hasRole('Super Admin'))
                            <div class="col-md-6 col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-14 mb-1">Nilai Stok Modal</div>
                                        </div>
                                        <div class="d-flex align-items-baseline">
                                            <div class="fs-22 mb-0 me-2 fw-semibold text-black">Rp {{ number_format($stockValue, 0, ',', '.') }}</div>
                                        </div>
                                        <a href="{{ route('all.product') }}" class="text-muted fs-12">
                                            Lihat Rincian Stok
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Produk Stok Rendah (<= 3)</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold {{ $lowStockCount > 0 ? 'text-danger' : 'text-black' }}">{{ $lowStockCount }} Item</div>
                                    </div>
                                    <a href="{{ route('all.product') }}" class="text-muted fs-12">
                                        Perlu Segera Restock
                                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            
        @endif
        {{-- Dasboard Penjahit --}}
        @if (Auth::user()->hasRole('Tailor'))
            <div class="row">
                <div class="col-12">
                    <div class="row g-3">

                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Pekerjaan Ditugaskan</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ $assignedJobs ?? 0 }} Transaksi</div>
                                    </div>
                                    <a href="{{ route('all.tailor') }}" class="text-muted fs-12">
                                        Lihat Semua Pekerjaan
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Selesai Bulan Ini</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-black">{{ $completedJobsThisMonth ?? 0 }} Transaksi</div>
                                    </div>
                                    <a href="{{ route('all.tailor') }}" class="text-muted fs-12">
                                        Lihat Riwayat
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-14 mb-1">Komisi Bulan Ini</div>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <div class="fs-22 mb-0 me-2 fw-semibold text-success">Rp {{ number_format($pendapatanPenjahit ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                    <a href="#" class="text-muted fs-12">
                                        Lihat Rincian Komisi
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif
    </div> <!-- container-fluid -->
</div>



@endsection
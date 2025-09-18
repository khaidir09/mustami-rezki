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
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Ringkasan Keuangan Keseluruhan (Bulan Ini)</h4>

                            <div class="row text-center">
                                
                                {{-- Angka Utama: Uang Bersih --}}
                                <div class="col-12 mb-4">
                                    <p class="text-muted mb-1">Uang Bersih Saat Ini</p>
                                    <h2 class="mb-0 text-primary">@rupiah($uangBersih)</h2>
                                    <small class="text-muted">(Profit Kotor - Total Pengeluaran)</small>
                                </div>

                                <hr>

                                {{-- Rincian Pendapatan & Pengeluaran --}}
                                <div class="col-12">
                                    <div class="row">

                                        {{-- Kolom Profit Kotor --}}
                                        <div class="col-md-3 col-6 border-end">
                                            <div class="my-3">
                                                <p class="text-muted mb-2">
                                                    <i data-feather="arrow-up" class="text-success"></i> Profit Kotor
                                                </p>
                                                <div class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($totalProfitKotor)</div>
                                            </div>
                                        </div>

                                        {{-- Kolom Gaji & Komisi --}}
                                        <div class="col-md-3 col-6 border-end">
                                            <div class="my-3">
                                                <p class="text-muted mb-2">
                                                    <i data-feather="arrow-down" class="text-danger"></i> Total Gaji & Komisi
                                                </p>
                                                <div class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($monthlyPayrollTotal)</div>
                                            </div>
                                        </div>

                                        {{-- Kolom Pengeluaran Operasional --}}
                                        <div class="col-md-3 col-6 border-end">
                                            <div class="my-3">
                                                <p class="text-muted mb-2">
                                                    <i data-feather="arrow-down" class="text-danger"></i> Total Pengeluaran
                                                </p>
                                                <div class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($monthlyExpenses)</div>
                                            </div>
                                        </div>

                                        {{-- Kolom Pembelian Barang --}}
                                        <div class="col-md-3 col-6">
                                            <div class="my-3">
                                                <p class="text-muted mb-2">
                                                    <i data-feather="arrow-down" class="text-danger"></i> Total Pembelian
                                                </p>
                                                <div class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($monthlyPurchasesTotal)</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Ringkasan Profit Bulan Ini</h4>
                            <div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-light text-success rounded-circle fs-3">
                                            <i data-feather="arrow-up"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Total Keseluruhan Profit</p>
                                        <h2 class="text-success mb-0">@rupiah($totalProfit)</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted mb-2">Pengembangan Modal</p>
                                            <h5 class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($totalModal)</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted mb-2">Dana Pribadi</p>
                                            <h5 class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($totalPribadi)</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted mb-2">Dana Sedekah</p>
                                            <h5 class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($totalSedekah)</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Ringkasan Pendapatan Bulan Ini</h4>
                            <div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-light text-success rounded-circle fs-3">
                                            <i data-feather="arrow-up"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Total Keseluruhan Pendapatan</p>
                                        <h2 class="text-success mb-0">@rupiah($omzetPenjualan + $omzetJahit + $omzetProduksi)</h2>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-2">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted mb-2">Penjualan</p>
                                            <h5 class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($omzetPenjualan)</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted mb-2">Jasa Jahit</p>
                                            <h5 class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($omzetJahit)</h5>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <p class="text-muted mb-2">Produksi</p>
                                            <h5 class="fs-22 mb-0 me-2 fw-semibold text-black">@rupiah($omzetProduksi)</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        {{-- Statistik Penjualan --}}
        @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
            <div class="row">
                <div class="col-12">
                    <div class="pb-3 d-flex align-items-sm-center flex-sm-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-18 fw-semibold m-0">Statistik Penjualan</h4>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-truncate font-size-14 mb-2">Total Transaksi</p>
                                            <h4 class="fs-22 me-2 fw-semibold text-black mb-2">{{ $totalSaleItem }} Item</h4>
                                            <p class="text-muted mb-0">Dari {{ $totalTransaksiPenjualan }} transaksi</p>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-light text-primary rounded-3">
                                                <i data-feather="shopping-cart"></i>  
                                            </span>
                                        </div>
                                    </div>                                            
                                </div>
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <p class="text-truncate font-size-14 mb-2">Total Profit Penjualan</p>
                                            <h4 class="fs-22 mb-2 me-2 fw-semibold text-success">@rupiah($totalProfitPenjualan)</h4>
                                            <p class="text-muted mb-0">Keuntungan bersih dari penjualan</p>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-light text-success rounded-3">
                                                <i data-feather="bar-chart-2"></i>  
                                            </span>
                                        </div>
                                    </div>                                            
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
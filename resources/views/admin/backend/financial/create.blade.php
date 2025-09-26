@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid my-0">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h2 class="fs-22 fw-semibold m-0">Verifikasi & Tutup Buku Bulanan</h2>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-2">
                        <a href="{{ route('financial.index') }}" class="btn btn-dark">Kembali</a>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h4 class="card-title">Rekapitulasi Keuangan Periode {{ \Carbon\Carbon::create($activeSummary->year, $activeSummary->month)->translatedFormat('F Y') }}</h4>
                                <p class="card-title-desc">Harap periksa semua rincian di bawah ini sebelum menutup buku periode ini.</p>
                            </div>

                            <div class="row mt-4">
                                {{-- KOLOM PEMASUKAN --}}
                                <div class="col-md-6">
                                    <div class="card border border-success">
                                        <div class="card-header bg-transparent border-success">
                                            <h5 class="my-0 text-success"><i data-feather="arrow-up" class="me-2"></i>Total Pemasukan</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">Penjualan Produk: <span class="float-end">@rupiah($salesIncome)</span></p>
                                            <p class="card-text">Jasa Jahit: <span class="float-end">@rupiah($tailorIncome)</span></p>
                                            <p class="card-text">Produksi: <span class="float-end">@rupiah($productionIncome)</span></p>
                                            @if ($externalIncome)
                                                <p class="card-text">Penerimaan Eksternal: <span class="float-end">@rupiah($externalIncome)</span></p>
                                            @endif
                                            <hr>
                                            <h5 class="card-title mt-2">Jumlah Pemasukan: <span class="float-end text-success">@rupiah($totalIncome)</span></h5>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOLOM PENGELUARAN --}}
                                <div class="col-md-6">
                                    <div class="card border border-danger">
                                        <div class="card-header bg-transparent border-danger">
                                            <h5 class="my-0 text-danger"><i data-feather="arrow-down" class="me-2"></i>Total Pengeluaran</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">Pembelian Barang: <span class="float-end">@rupiah($purchaseExpense)</span></p>
                                            <p class="card-text">Biaya Operasional: <span class="float-end">@rupiah($operationalExpense)</span></p>
                                            <p class="card-text">Gaji & Komisi: <span class="float-end">@rupiah($payrollExpense)</span></p>
                                            <hr>
                                            <h5 class="card-title mt-2">Jumlah Pengeluaran: <span class="float-end text-danger">@rupiah($totalExpense)</span></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- BAGIAN KALKULASI AKHIR --}}
                            <div class="row justify-content-center mt-4">
                                <div class="col-lg-8">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <tbody>
                                                <tr>
                                                    <td>Saldo Awal Bulan</td>
                                                    <td class="text-end">@rupiah($openingBalance)</td>
                                                </tr>
                                                <tr>
                                                    <td>(+) Total Pemasukan</td>
                                                    <td class="text-end text-success">@rupiah($totalIncome)</td>
                                                </tr>
                                                <tr>
                                                    <td>(-) Total Pengeluaran</td>
                                                    <td class="text-end text-danger">@rupiah($totalExpense)</td>
                                                </tr>
                                                <tr class="table-light">
                                                    <th class="fs-5">Perkiraan Saldo Akhir</th>
                                                    <td class="text-end fs-5 fw-bold">@rupiah($closingBalance)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- TOMBOL AKSI --}}
                            <div class="text-center mt-4">
                                <form action="{{ route('financial.store') }}" method="post" onsubmit="return confirm('Anda yakin ingin menutup buku untuk periode ini? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    <input type="hidden" name="year" value="{{ $activeSummary->year }}">
                                    <input type="hidden" name="month" value="{{ $activeSummary->month }}">
                                    
                                    <button class="btn btn-primary btn-lg waves-effect waves-light" type="submit">
                                        <i class="ri-lock-line"></i> Konfirmasi & Tutup Buku
                                    </button>
                                </form>
                            </div>
                            
                        </div>
                    </div> 
                </div> 
            </div>

        </div>
    </div>
</div>
@endsection
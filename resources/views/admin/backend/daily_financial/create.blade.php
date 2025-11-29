@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid my-0">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h2 class="fs-22 fw-semibold m-0">Verifikasi & Tutup Buku Harian</h2>
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
                                <h4 class="card-title">Ringkasan Arus Kas Harian {{ \Carbon\Carbon::create($date)->translatedFormat('d F Y') }}</h4>
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

                            <div class="row mt-4">
                                <div class="col-xl-8 mx-auto">
                                    <form action="{{ route('daily.financial.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                                        <table class="table table-bordered mb-4">
                                            <tbody>
                                                <tr>
                                                    <th class="bg-light">Saldo Awal Hari</th>
                                                    <td class="text-end">
                                                        Rp {{ number_format($openingBalance, 0, ',', '.') }}
                                                        <input type="hidden" name="opening_balance" value="{{ $openingBalance }}">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light text-success">Total Pemasukan</th>
                                                    <td class="text-end text-success">
                                                        + Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                                        <input type="hidden" name="total_income" value="{{ $totalIncome }}">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light text-danger">Total Pengeluaran</th>
                                                    <td class="text-end text-danger">
                                                        - Rp {{ number_format($totalExpense, 0, ',', '.') }}
                                                        <input type="hidden" name="total_expense" value="{{ $totalExpense }}">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-dark text-white">Saldo Akhir Hari</th>
                                                    <td class="text-end fw-bold bg-dark text-white">
                                                        Rp {{ number_format($closingBalance, 0, ',', '.') }}
                                                        <input type="hidden" name="closing_balance" value="{{ $closingBalance }}">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Catatan (Opsional)</label>
                                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Apakah Anda yakin ingin melakukan tutup buku untuk tanggal {{ $date->format('d F Y') }}? Data tidak bisa diubah setelah ditutup.')">
                                                <i class="bx bx-check-circle"></i> Lakukan Tutup Buku
                                            </button>
                                            <a href="{{ route('daily.financial.index') }}" class="btn btn-secondary">Batal</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                        </div>
                    </div> 
                </div> 
            </div>

        </div>
    </div>
</div>
@endsection
@extends('admin.admin_master')
@section('admin')

<div class="content">
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Detail Transaksi Jahit</h4>
            </div>
            <div class="text-end">
                {{-- Tambahkan tombol aksi lain di sini jika perlu, misal Print --}}
                <a href="{{ route('all.tailor') }}" class="btn btn-dark">Kembali</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Info Pelanggan:</h5>
                                <p class="mb-1"><strong>Nama:</strong> {{ $transaction->customer->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Telepon:</strong> {{ $transaction->customer->phone ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Alamat:</strong> {{ $transaction->customer->address ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h5>Info Transaksi:</h5>
                                <p class="mb-1"><strong>Kode:</strong> {{ $transaction->transaction_code }}</p>
                                <p class="mb-1"><strong>Penjahit:</strong> {{ $transaction->tailor->name ?? 'Belum Ditugaskan' }}</p>
                                <p class="mb-1"><strong>Tgl. Masuk:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') }}</p>
                                <p class="mb-1"><strong>Target Selesai:</strong> {{ $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->format('d F Y') : '-' }}</p>
                                <p class="mb-1"><strong>Status:</strong>
                                     @switch($transaction->status)
                                        @case('Antrian')
                                            <span class="badge text-bg-warning">Antrian</span>
                                            @break
                                        @case('Dikerjakan')
                                            <span class="badge text-bg-primary">Dikerjakan</span>
                                            @break
                                        @case('Selesai')
                                            <span class="badge text-bg-success">Selesai</span>
                                            @break
                                        @case('Diambil')
                                            <span class="badge text-bg-dark">Diambil</span>
                                            @break
                                        @default
                                            <span class="badge text-bg-secondary">{{ $transaction->status }}</span>
                                    @endswitch
                                </p>
                            </div>
                        </div>

                        <hr>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Rincian Jasa:</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Jenis</th>
                                                <th>Komponen</th>
                                                <th class="text-center">Jumlah</th>
                                                <th class="text-end">Harga Satuan</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($transaction->items as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $item->type->name ?? '-' }}</td>
                                                <td>{{ $item->nama_komponen }}</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada item jasa pada transaksi ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Catatan:</h5>
                                <p>{{ $transaction->description ?: 'Tidak ada catatan.' }}</p>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td>Total Harga</td>
                                            <td class="text-end fw-bold">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Biaya Modal</td>
                                            <td class="text-end">Rp {{ number_format($transaction->cost_price, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Profit</td>
                                            <td class="text-end fw-bold text-success">Rp {{ number_format($transaction->profit, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Jumlah Dibayar</td>
                                            <td class="text-end">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Sisa Bayar</td>
                                            <td class="text-end fw-bold text-danger">Rp {{ number_format($transaction->due_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> </div> </div> </div> </div> </div> @endsection
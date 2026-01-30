@extends('admin.admin_master')
@section('admin')

<div class="content">

    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Laporan Distribusi Profit</h4>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Omzet</h5>
                        <h3 class="fs-22 fw-bold text-primary">@rupiah($totalOmzet)</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Profit</h5>
                        <h3 class="fs-22 fw-bold text-success">@rupiah($totalProfit)</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <form action="{{ route('profit.distribution.report') }}" method="GET" class="mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                                     <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('profit.distribution.report') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>ID Transaksi</th>
                                        <th>Tipe Transaksi</th>
                                        <th>Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($distributions as $key => $item)
                                    <tr>
                                        <td>{{ $key+1  }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                                        <td>
                                            @if($item->transaction_type == 'App\Models\Sale')
                                                <a href="{{ route('details.sale', $item->transaction_id) }}">SALE-{{ $item->transaction_id }}</a>
                                            @elseif ($item->transaction_type == 'App\Models\TailorTransaction')
                                                {{-- Tambahkan link untuk tipe lain jika ada, misal Jasa Jahit --}}
                                                <a href="{{ route('details.tailor', $item->transaction_id) }}">JAHIT-{{ $item->transaction_id }}</a>
                                            @elseif ($item->transaction_type == 'App\Models\TailorTransactionProduct')
                                                <a href="{{ route('details.tailor', $item->transaction_id) }}">SIT-{{ $item->transaction_id }}</a>
                                            @else
                                                <a href="{{ route('all.production', $item->transaction_id) }}">PRODUKSI-{{ $item->transaction_id }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Menampilkan nama model yang lebih ramah dibaca --}}
                                            {{ str_replace('App\Models\\', '', $item->transaction_type) }}
                                        </td>
                                        <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data ditemukan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div> 
                    </div>
                 </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('admin.admin_master')
@section('admin')

<div class="content">

    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Transaksi Jasa Jahit</h4>
            </div>

            @if ($isAdmin)
                <div class="text-end">
                    <a href="{{ route('add.tailor') }}" class="btn btn-secondary">Tambah Transaksi Jahit</a>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('all.tailor') }}" method="GET" class="mb-4">
                            <div class="row align-items-end g-2">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Cari Kode / Pelanggan</label>
                                    <input type="text" name="search" id="search" class="form-control" value="{{ $search }}" placeholder="JAHIT-0801 atau nama">
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">Semua</option>
                                        @foreach (['Antrian', 'Dikerjakan', 'Selesai', 'Diambil'] as $statusOption)
                                            <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label">Tgl. Masuk Dari</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date" class="form-label">Sampai</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('all.tailor') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                            <div class="form-text mt-2">
                                @if ($search !== '')
                                    Menampilkan hasil pencarian <strong>"{{ $search }}"</strong> dari seluruh periode (filter tanggal diabaikan saat mencari).
                                @else
                                    Rentang tanggal masuk {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }} &mdash; total {{ number_format($transactions->total(), 0, ',', '.') }} transaksi.
                                @endif
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered nowrap">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        @foreach ($sortColumns as $column => $label)
                                            <th>
                                                {{-- Klik header membalik arah urut kolom aktif, kolom lain mulai dari menaik --}}
                                                <a href="{{ route('all.tailor', array_merge(request()->except(['sort', 'dir', 'page']), ['sort' => $column, 'dir' => $sort === $column && $dir === 'asc' ? 'desc' : 'asc'])) }}"
                                                   class="text-body text-decoration-none">
                                                    {{ $label }}
                                                    @if ($sort === $column)
                                                        <span class="mdi mdi-arrow-{{ $dir === 'asc' ? 'up' : 'down' }}"></span>
                                                    @else
                                                        <span class="mdi mdi-unfold-more-horizontal text-muted"></span>
                                                    @endif
                                                </a>
                                            </th>
                                        @endforeach
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactions as $key => $item)
                                    <tr>
                                        <td>{{ $transactions->firstItem() + $key }}</td>
                                        <td>
                                            <a title="Lihat Detail" href="{{ route('details.tailor', $item->id) }}">{{ $item->transaction_code }}</a>
                                        </td>
                                        <td>{{ $item->customer->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($item->work_type == 'Internal' && $item->tailor)
                                                {{ $item->tailor->name }}
                                            @elseif($item->work_type == 'Eksternal' && $item->supplier)
                                                {{ $item->supplier->name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->transaction_date)->format('d-m-Y') }}</td>
                                        <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d-m-Y') : '-' }}</td>
                                        {{-- Hitung total biaya keseluruhan jasa jahit dan harga produk yang digunakan dari stok toko --}}
                                        <td>@rupiah($item->total_price + ($item->sold_products_sum_subtotal ?? 0))</td>
                                        {{-- Hitung total komponen dari jasa jahit --}}
                                        <td>{{ $item->items_count }}</td>
                                        <td>
                                            @switch($item->status)
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
                                                    <span class="badge text-bg-dark">Diambil {{ $item->picked_up_at ? \Carbon\Carbon::parse($item->picked_up_at)->format('d-m-Y') : '-' }}</span>
                                                    @break
                                                @default
                                                    <span class="badge text-bg-secondary">{{ $item->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if ($isAdmin)
                                            <a title="Edit" href="{{ route('edit.tailor', $item->id) }}" class="btn btn-success btn-sm"> <span class="mdi mdi-book-edit mdi-18px"></span> </a>
                                                @if($item->customer->phone)
                                                    {{-- Nota WA dibangun di server saat diklik (route wa.tailor), bukan saat daftar dirender --}}
                                                    <a title="Kirim Nota via WA"
                                                    href="{{ route('wa.tailor', $item->id) }}"
                                                    target="_blank"
                                                    class="btn btn-primary btn-sm">
                                                        <span class="mdi mdi-message-text mdi-18px"></span>
                                                    </a>
                                                @endif
                                            @endif
                                            <a title="Delete" href="{{ route('delete.tailor', $item->id) }}" class="btn btn-danger btn-sm" id="delete"><span class="mdi mdi-delete mdi-18px"></span></a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            Tidak ada transaksi pada filter ini. Coba ubah rentang tanggal atau gunakan kolom pencarian.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($transactions->hasPages())
                            <div class="mt-3">
                                {{ $transactions->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
    </div> 
</div> 
@endsection
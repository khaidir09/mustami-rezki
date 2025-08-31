@extends('admin.admin_master')
@section('admin')

<div class="content">

    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Transaksi Jasa Jahit</h4>
            </div>

            <div class="text-end">
                <a href="{{ route('add.tailor') }}" class="btn btn-secondary">Tambah Transaksi Jahit</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Kode Transaksi</th>
                                        <th>Pelanggan</th>
                                        <th>Penjahit</th>
                                        <th>Tgl. Masuk</th>
                                        <th>Tgl. Selesai</th>
                                        <th>Total Harga</th>
                                        <th>Total Komponen</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->transaction_code }}</td>
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
                                        <td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                        <td>{{ $item->items->count() }}</td>
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
                                                    <span class="badge text-bg-dark">Diambil</span>
                                                    @break
                                                @default
                                                    <span class="badge text-bg-secondary">{{ $item->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a title="Details" href="{{ route('details.tailor', $item->id) }}" class="btn btn-info btn-sm"> <span class="mdi mdi-eye-circle mdi-18px"></span> </a>
                                            @if (Auth::user()->hasRole('Super Admin'))
                                            <a title="Edit" href="{{ route('edit.tailor', $item->id) }}" class="btn btn-success btn-sm"> <span class="mdi mdi-book-edit mdi-18px"></span> </a>
                                            <a title="Delete" href="{{ route('delete.tailor', $item->id) }}" class="btn btn-danger btn-sm" id="delete"><span class="mdi mdi-delete-circle mdi-18px"></span></a>
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
        
    </div> 
</div> 
@endsection

@push('scripts')
    <script>
        $("#datatable").dataTable({
            "columnDefs": [{
                "sortable": false,
                "targets": [10]
            }],
            "order": [[0, "asc"]]
        });
    </script>
@endpush
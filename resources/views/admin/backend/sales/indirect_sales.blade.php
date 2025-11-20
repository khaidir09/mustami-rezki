@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Penjualan dari Transaksi Jahit</h4>
            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('all.sale') }}" class="badge bg-dark fs-12 py-2">Penjualan Langsung</a>
            <a href="{{ route('indirect.sale') }}" class="badge bg-primary fs-12 py-2">Penjualan dari Jahit</a>
        </div>

        <!-- Datatables  -->
        <div class="row">
            <div class="col-12">
                <div class="card">

<div class="card-body">
    <div class="table-responsive">
        <table id="datatable" class="table table-bordered">
        <thead>
        <tr>
            <th>No.</th>
            <th>Nota Jahit</th>
            <th>Pelanggan</th>
            <th>Produk</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Total Penjualan</th>
            <th>Tanggal</th>
        </tr>
        </thead>
        <tbody>
    @foreach ($allData as $key=> $item) 
    <tr>
        <td>{{ $key+1 }}</td>
        <td>
            <a href="{{ route('details.tailor', $item->tailor_transaction_id) }}">#{{ $item->tailorTransaction->transaction_code }}</a>
        </td>
        <td>{{ $item->tailorTransaction->customer->name }}</td>
        <td>{{ $item->product_name }}</td>
        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td> 
        <td>{{ $item->quantity }}</td>
        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y, H:i') }}</td> 
    </tr>
    @endforeach 
                
        </tbody>
    </table>
    </div>
</div>

                </div>
            </div>
        </div>


     

    </div> <!-- container-fluid -->

</div> <!-- content -->



@endsection

@push('scripts')
    <script>
        $("#datatable").dataTable({
            "columnDefs": [{
                "sortable": false,
                "targets": [5]
            }],
            "order": [[0, "asc"]]
        });
    </script>
@endpush
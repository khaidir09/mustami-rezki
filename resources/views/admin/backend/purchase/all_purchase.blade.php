@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Pembelian Barang</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2">
                     <a href="{{ route('add.purchase') }}" class="btn btn-secondary">Tambah Pembelian Barang</a>
                </ol>
            </div>
        </div>

        <!-- Datatables  -->
        <div class="row">
            <div class="col-12">
                <div class="card">

<div class="card-body">
    <div class="table-responsive">
        <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
        <thead>
        <tr>
            <th>No.</th>
            <th>Supplier</th>
            <th>Grand Total</th>
            <th>Tanggal</th> 
            <th>Status</th> 
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
    @foreach ($allData as $key=> $item) 
    <tr>
        <td>{{ $key+1 }}</td>
        <td>{{ $item->supplier->name }}</td>
        <td>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}</td>
        <td>{{ $item->status }}</td>
        <td>
   <a title="Details" href="{{ route('details.purchase',$item->id) }}" class="btn btn-info btn-sm"> <span class="mdi mdi-eye-circle mdi-18px"></span> </a> 

   <a title="PDF Invoice" href="{{ route('invoice.purchase',$item->id) }}" class="btn btn-primary btn-sm"> <span class="mdi mdi-download-circle mdi-18px"></span> </a> 

    {{-- <a title="Edit" href="{{ route('edit.purchase',$item->id) }}" class="btn btn-success btn-sm"> <span class="mdi mdi-book-edit mdi-18px"></span> </a>   --}}

    <a title="Delete" href="{{ route('delete.purchase',$item->id) }}" class="btn btn-danger btn-sm" id="delete"><span class="mdi mdi-delete-circle  mdi-18px"></span></a>    
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


     

    </div> <!-- container-fluid -->

</div> <!-- content -->



@endsection
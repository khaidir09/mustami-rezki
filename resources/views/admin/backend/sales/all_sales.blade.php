@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Penjualan</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('add.sale') }}" class="btn btn-secondary">Tambah Penjualan</a>
                </ol>
            </div>
        </div>

        <!-- Datatables  -->
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-header">
                         
                    </div><!-- end card header -->

<div class="card-body">
    <div class="table-responsive">
        <table id="datatable" class="table table-bordered">
        <thead>
        <tr>
            <th>No.</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Total Penjualan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
    @foreach ($allData as $key=> $item) 
    <tr>
        <td>{{ $key+1 }}</td>
        <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
        <td>{{ $item->customer->name }}</td>
        <td>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td> 
        <td>
            @if ($item->status == 'Pending')
                <span class="badge text-bg-danger">{{ $item->status }}</span>
            @elseif ($item->status == 'Terjual')
                <span class="badge text-bg-success">{{ $item->status }}</span>
            @else
                <span class="badge text-bg-warning">{{ $item->status }}</span>
            @endif
        </td>
        <td>
   <a title="Details" href="{{ route('details.sale',$item->id) }}" class="btn btn-info btn-sm"> <span class="mdi mdi-eye-circle mdi-18px"></span> </a> 

   <a title="PDF Invoice" href="{{ route('invoice.sale',$item->id) }}" class="btn btn-primary btn-sm"> <span class="mdi mdi-download-circle mdi-18px"></span> </a> 

    {{-- <a title="Edit" href="{{ route('edit.sale',$item->id) }}" class="btn btn-success btn-sm"> <span class="mdi mdi-book-edit mdi-18px"></span> </a>   --}}

    <a title="Delete" href="{{ route('delete.sale',$item->id) }}" class="btn btn-danger btn-sm" id="delete"><span class="mdi mdi-delete-circle  mdi-18px"></span></a>    
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
@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Pengguna</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2">
                     <a href="{{ route('add.admin') }}" class="btn btn-secondary">Tambah Pengguna</a>
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
    <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
        <thead>
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>Email</th> 
            <th>Peran</th> 
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
           @foreach ($alladmin as $key=> $item) 
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td> 
                <td> 
                @foreach ($item->roles as $role)
                    <span class="badge badge-pill bg-primary">{{ $role->name ?? 'N/A' }}</span>
                @endforeach
                </td>
                <td>
            <a href="{{ route('edit.admin',$item->id) }}" class="btn btn-success btn-sm">Edit</a>  
            <a href="{{ route('delete.admin',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Hapus</a>    
                </td> 
            </tr>
            @endforeach 
                
        </tbody>
    </table>
</div>

                </div>
            </div>
        </div>


     

    </div> <!-- container-fluid -->

</div> <!-- content -->



@endsection
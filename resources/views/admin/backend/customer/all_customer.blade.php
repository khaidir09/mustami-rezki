@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Pelanggan</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0"> 
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#standard-modal"> Tambah Pelanggan </button>
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
            <th>Nama Pelanggan</th>
            <th>Nomor HP/WA</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
           @foreach ($customer as $key=> $item) 
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->phone }}</td>
                <td>{{ Str::limit($item->address, 50, '...')  }}</td>
                <td> 

            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#customer" id="{{ $item->id }}" onclick="customerEdit(this.id)"> Edit</button>

            <a href="{{ route('delete.customer',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Hapus</a>    
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





 <!-- Default Modal -->
 <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="standard-modalLabel">Pelanggan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

    <div class="modal-body">
    <form action="{{ route('store.customer') }}" method="post">
    @csrf

    <div class="form-group mb-3 col-md-12">
        <label for="input1" class="form-label">Nama Pelanggan</label>
        <input type="text" class="form-control" name="name"   id="input1"> 
    </div>

    <div class="form-group mb-3 col-md-12">
        <label for="input2" class="form-label">Nomor HP/WA</label>
        <input type="text" class="form-control" name="phone" placeholder="Gunakan format 62xxxxxxxxxx" id="input2"> 
    </div>

    <div class="form-group col-md-12">
        <label for="input3" class="form-label">Alamat</label>
        <textarea name="address" class="form-control" id="input3"></textarea>
    </div>
            
    </div>
    <div class="modal-footer"> 
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>


        </div>
    </div>
</div>






<!-- edit category Modal -->
<div class="modal fade" id="customer" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="standard-modalLabel">Edit Pelanggan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

    <div class="modal-body">
    <form action="{{ route('update.customer') }}" method="post">
    @csrf
     <input type="hidden" name="cust_id" id="cust_id">

    <div class="form-group mb-3 col-md-12">
        <label for="input1" class="form-label">Nama Pelanggan</label>
        <input type="text" name="name" class="form-control" id="cust"> 
    </div>
    
    <div class="form-group mb-3 col-md-12">
            <label for="input2" class="form-label">Nomor HP/WA</label>
            <input type="text" class="form-control" name="phone"  id="phon" > 
        </div>

        <div class="form-group col-md-12">
            <label for="input3" class="form-label">Alamat</label>
            <textarea name="address" class="form-control" id="addr"></textarea>
        </div>
            
    </div>
    <div class="modal-footer"> 
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>


        </div>
    </div>
</div>


<script>
    function customerEdit(id){
        $.ajax({
            type: 'GET',
            url: '/edit/customer/'+id,
            dataType: 'json',

            success:function(data){
                // console.log(data);
                 $('#cust').val(data.name);
                 $('#phon').val(data.phone); 
                $('#addr').val(data.address);
                $('#cust_id').val(data.id);
            }
        })
    }
</script>





@endsection
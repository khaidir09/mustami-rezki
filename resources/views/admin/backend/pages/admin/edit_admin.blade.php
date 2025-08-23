@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit Admin</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.admin') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>

        <!-- Form Validation -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('update.admin',$admin->id) }}" method="post" class="row g-3" enctype="multipart/form-data">
                            @csrf

                            <div class="col-md-6">
                                <label for="validationDefault01" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" value="{{ $admin->name }}"  >  
                            </div>

                            <div class="col-md-6">
                                <label for="validationDefault01" class="form-label">Email</label>
                                <input type="emal" class="form-control" name="email"  value="{{ $admin->email }}"  >  
                            </div>
                    

                            <div class="col-md-6">
                                <label for="validationDefault01" class="form-label">Peran</label>
                                <select name="roles" class="form-select" id="example-select">
                                    <option value="" selected>Pilih Peran</option>
                                    @foreach ($roles as $role) 
                                    <option value="{{ $role->id }}" {{ $admin->hasRole($role->name) ? 'selected' : '' }} >{{ $role->name }}</option> 
                                    @endforeach
                                </select>
                            </div>
                    
                    
                                
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Simpan</button>
                            </div>
                        </form>
                    </div> <!-- end card-body -->
                </div> <!-- end card-->
            </div> <!-- end col -->

          
        </div>

        

    </div> <!-- container-fluid -->

</div>
 

@endsection
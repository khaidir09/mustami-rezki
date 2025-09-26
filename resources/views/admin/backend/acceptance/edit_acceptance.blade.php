@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Penerimaan</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2">
                     <a href="{{ route('all.acceptance') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         <div class="card">
            <div class="card-body">
<form action="{{ route('update.acceptance') }}" method="post" enctype="multipart/form-data">
   @csrf
   <input type="hidden" name="id" value="{{ $editData->id }}">

   <div class="row">
      <div class="col-md-6 mb-3">
         <label class="form-label">Tanggal</label>
         <input type="date" class="form-control" name="date" value="{{ $editData->date }}"> 
      </div>
      <div class="col-md-6 mb-3">
         <label class="form-label">Deskripsi</label>
         <input type="text" name="description" class="form-control" value="{{ $editData->description }}">
      </div>
      <div class="col-md-6 mb-3">
         <label class="form-label">Jumlah</label>
         <input type="number" name="amount" class="form-control" min="0" value="{{ $editData->amount }}">
      </div>
      <div class="col-xl-12">
         <div class="d-flex justify-content-start">
            <button class="btn btn-primary me-3" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('all.acceptance') }}">Batal</a>
         </div>
      </div>
   </div>
</form>
</div>
         </div>
      </div>
   </div>
</div>
 

@endsection
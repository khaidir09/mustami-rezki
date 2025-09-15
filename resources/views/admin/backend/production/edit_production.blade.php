@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Produksi</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2">
                     <a href="{{ route('all.production') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         <div class="card">
            <div class="card-body">
<form action="{{ route('update.production', $production->id) }}" method="post">
   @csrf
   <input type="hidden" name="id" value="{{ $production->id }}">
   <input type="hidden" name="user_id" value="{{ $production->user_id }}">

   <div class="row">
      <div class="col-md-6 mb-3">
         <label class="form-label">Tanggal</label>
         <input type="date" class="form-control" name="date" value="{{ $production->date }}"> 
      </div>
      <div class="col-md-6 mb-3">
         <label class="form-label">Nama Produksi</label>
         <input type="text" name="name" class="form-control" value="{{ $production->name }}">
      </div>
      <div class="col-md-6 mb-3">
         <label class="form-label">Jumlah</label>
         <input type="number" name="quantity" class="form-control" min="0" value="{{ $production->quantity }}">
      </div>
      <div class="col-md-6 mb-3">
         <label class="form-label">Harga</label>
         <input type="number" name="price" class="form-control" min="0" value="{{ $production->price }}">
      </div>
      <div class="col-xl-12">
         <div class="d-flex justify-content-start">
            <button class="btn btn-primary me-3" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('all.production') }}">Batal</a>
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
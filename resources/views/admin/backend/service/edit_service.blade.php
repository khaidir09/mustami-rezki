@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Jasa Jahit</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.service') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         <div class="card">
            <div class="card-body">
<form action="{{ route('update.service') }}" method="post">
   @csrf
   <input type="hidden" name="id" value="{{ $editData->id }}">

   <div class="row">
      <div class="col-md-6 mb-3">
         <label class="form-label">Komponen Jasa:</label>
         <input type="text" name="name" class=" form-control" value="{{ $editData->name }}">
      </div>
      <div class="col-md-6 mb-3">
         <label class="form-label">Biaya Dasar:</label>
         <input type="number" name="base_price" class="form-control" min="0" value="{{ $editData->base_price }}">
      </div>
      <div class="col-md-6 mb-3">
         <div class="form-group w-100">
            <label class="form-label" for="formBasic">Status :</label>
            <select name="is_active" id="is_active" class="form-control form-select">
               <option value="1" {{ $editData->is_active ? 'selected' : ''}}>Aktif</option>
               <option value="0" {{ !$editData->is_active ? 'selected' : ''}}>Tidak Aktif</option>
            </select>
         </div>
      </div>
      <div class="col-xl-12">
         <div class="d-flex justify-content-start">
            <button class="btn btn-primary me-3" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('all.service') }}">Batal</a>
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
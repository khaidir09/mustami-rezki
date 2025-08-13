@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Produk</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.product') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         <div class="card">
            <div class="card-body">
<form action="{{ route('update.product') }}" method="post" enctype="multipart/form-data">
   @csrf
    <input type="hidden" name="id" value="{{ $editData->id }}"  >

   <div class="row">
      <div class="col-xl-8">
         <div class="card">
            <div class="row">
               <div class="col-md-6 mb-3">
                  <div class="form-group w-100">
                     <label class="form-label" for="formBasic">Kategori Produk :</label>
                     <select name="category_id" id="category_id" class="form-control form-select">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $editData->category_id ? 'selected' : ''}}>{{ $item->category_name }}</option>
                        @endforeach
                     </select>
                    
                  </div>
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Kode:</label>
                  <input type="text" name="code" class=" form-control" value="{{ $editData->code }}">
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Nama Produk:</label>
                  <input type="text" name="name" placeholder="Masukkan nama" class="form-control" value="{{ $editData->name }}">  
               </div>
               <div class="col-md-6 mb-3">
                  <div class="form-group w-100">
                     <label class="form-label" for="formBasic">Satuan :</label>
                     <select name="satuan" id="satuan" class="form-control form-select">
                        <option value="{{ $editData->satuan }}" {{ $editData->satuan ? 'selected' : ''}}>{{ $editData->satuan }}</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Meter">Meter</option>
                        <option value="Gulung">Gulung</option>
                        <option value="Lusin">Lusin</option>
                        <option value="Set">Set</option>
                        <option value="Kg">Kg</option>
                        <option value="Botol">Botol</option>
                        <option value="Unit">Unit</option>
                        <option value="Pack">Pack</option>
                        <option value="Roll">Roll</option>
                     </select>
                  </div>
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Modal:</label>
                  <input type="number" name="modal" class="form-control" min="0" value="{{ $editData->modal }}">
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Harga Jual:</label>
                  <input type="number" name="price" class="form-control" min="0" value="{{ $editData->price }}">
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Stok:</label>
                  <input type="number" name="product_qty" class="form-control" min="1" value="{{ $editData->product_qty }}">
               </div>

               <div class="col-md-6 mb-3">
                  <label class="form-label">Peringatan Stok Minimal:</label>
                  <input type="number" name="stock_alert" class="form-control" min="0" value="{{ $editData->stock_alert }}">
                   
               </div>

               <div class="col-md-12">
                  <label class="form-label">Notes: </label>
                  <textarea class="form-control" name="note" rows="3" placeholder="Enter Notes">{{ $editData->note }}</textarea>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xl-4">
         <div class="card">
            <label class="form-label">Foto-foto Produk: <span class="text-danger">*</span></label>
               <div class="mb-3">
                  <input name="image[]" accept=".png, .jpg, .jpeg" multiple="" type="file" id="multiImg" class="upload-input-file form-control">
               </div>
                        
            <div class="row" id="preview_img">
               @if (isset($editData) && $editData->images->count() > 0)
                  @foreach ($editData->images as $img)
                     <div class="col-md-3 mb-2">
                           <img src="{{ asset($img->image) }}" alt="Product image" class="img-thumbnail">
                     
                     <div class="form-check mt-1">
                           <input class="form-check-input" type="checkbox" name="remove_image[]" value="{{ $img->id }}" id="remove_image_{{ $img->id }}">
                           <label for="remove_image_{{ $img->id }}" class="form-check-label">Hapus</label> 
                     </div>

                     </div> 
                  @endforeach
               @endif
            </div>
         </div>
      </div>
      <div class="col-xl-12">
         <div class="d-flex justify-content-start">
            <button class="btn btn-primary me-3" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('all.product') }}">Batal</a>
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
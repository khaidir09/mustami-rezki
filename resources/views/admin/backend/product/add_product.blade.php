@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Tambah Produk</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.product') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         <div class="card">
            <div class="card-body">
<form action="{{ route('store.product') }}" method="post" enctype="multipart/form-data">
   @csrf
   <div class="row">
      <div class="col-xl-8">
         <div class="card">
            <div class="row">
               <div class="col-md-6 mb-3">
                  <div class="form-group w-100">
                     <label class="form-label" for="formBasic">Kategori Produk : <span class="text-danger">*</span></label>
                     <select name="category_id" id="category_id" class="form-control form-select">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $item)
                        <option value="{{ $item->id }}">{{ $item->category_name }}</option>
                        @endforeach
                     </select>
                  </div>
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Kode:</label>
                  <input type="text" name="code" class=" form-control" placeholder="Masukkan kode">
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Nama Produk:  <span class="text-danger">*</span></label>
                  <input type="text" name="name" placeholder="Masukkan nama" class="form-control">  
               </div>
               <div class="col-md-6 mb-3">
                  <div class="form-group w-100">
                     <label class="form-label" for="formBasic">Satuan : <span class="text-danger">*</span></label>
                     <select name="satuan" id="satuan" class="form-control form-select">
                        <option value="">Pilih Satuan</option>
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
                  <label class="form-label">Modal: <span class="text-danger">*</span></label>
                  <input type="number" name="modal" class="form-control" placeholder="Masukkan harga beli" min="0" required>
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Harga Jual: <span class="text-danger">*</span></label>
                  <input type="number" name="price" class="form-control" placeholder="Masukkan harga jual" min="0" required>
               </div>
               <div class="col-md-6 mb-3">
                  <label class="form-label">Stok: <span class="text-danger">*</span></label>
                  <input type="number" name="product_qty" class="form-control" placeholder="Masukkan stok" min="1" required>
               </div>

               <div class="col-md-6 mb-3">
                  <label class="form-label">Peringatan Stok Minimal:</label>
                  <input type="number" name="stock_alert" class="form-control" placeholder="Masukkan stok minimal" min="0" required>
                   
               </div>
               <div class="col-md-12">
                  <label class="form-label">Catatan: </label>
                  <textarea class="form-control" name="note" rows="3" placeholder="Masukkan catatan"></textarea>
               </div>
            </div>
         </div>
      </div>
      <div class="col-xl-4">
         <div class="card">
            <label class="form-label">Foto-foto produk: <span class="text-danger">*</span></label>
            <div class="mb-3">
               <input name="image[]" accept=".png, .jpg, .jpeg" multiple="" type="file" id="multiImg" class="upload-input-file form-control">
            </div>
               
            <div class="row" id="preview_img"></div>
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

<script>
    document.getElementById('multiImg').addEventListener('change', function(event) {
        const previewContainer = document.getElementById('preview_img');
        previewContainer.innerHTML = ''; // Clear previous previews

        const files = Array.from(event.target.files); // Convert FileList to Array
        const input = event.target;

        files.forEach((file, index) => {
            // Check if the file is an image
            if (file.type.match('image.*')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // Create preview container
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';

                    // Create image
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-fluid rounded';
                    img.style.maxHeight = '150px';
                    img.alt = 'Image Preview';

                    // Create remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-danger btn-sm position-absolute';
                    removeBtn.style.top = '10px';
                    removeBtn.style.right = '10px';
                    removeBtn.innerHTML = '&times;'; // Cross icon
                    removeBtn.title = 'Remove Image';

                    // Remove button functionality
                    removeBtn.addEventListener('click', function() {
                        col.remove(); // Remove the image preview
                        // Update the file input by creating a new FileList
                        const newFiles = files.filter((_, i) => i !== index);
                        const dataTransfer = new DataTransfer();
                        newFiles.forEach(f => dataTransfer.items.add(f));
                        input.files = dataTransfer.files;
                    });

                    // Create wrapper for positioning
                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'relative';
                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);

                    col.appendChild(wrapper);
                    previewContainer.appendChild(col);
                };

                reader.readAsDataURL(file);
            }
        });
    });
</script>

@endsection
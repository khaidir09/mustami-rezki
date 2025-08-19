@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
         <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Buat Penjualan</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.sale') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         

         <div class="card">
            <div class="card-body">
               <form action="{{ route('store.sale')}}" method="post" enctype="multipart/form-data">
                  @csrf
                  <div class="row">
                     <div class="col-xl-12">
                        <div class="card">
                           <div class="row">
                              <div class="col-md-4 mb-3">
                                 <label class="form-label">Tanggal:  <span class="text-danger">*</span></label>
                                 <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control">
                                 @error('date')
                                 <span class="text-danger">{{ $message }}</span>
                                 @enderror
                              </div>

                              <div class="col-md-4 mb-3">
                                 <div class="form-group w-100">
                                    <label class="form-label" for="formBasic">Pelanggan : <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-control form-select select2">
                                       <option value="">Pilih Pelanggan</option>
                                       @foreach ($customers as $item)
                                       <option value="{{ $item->id }}">{{ $item->name }}</option>
                                       @endforeach
                                    </select>
                                    @error('customer_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-12 mb-3">
                                 <label class="form-label">Produk:</label>
                                 <div class="input-group">
                                       <span class="input-group-text">
                                          <i class="fas fa-search"></i>
                                       </span>
                                       <input type="search" id="product_search" name="search" class="form-control" placeholder="Cari produk berdasarkan nama atau kode">
                                 </div>
                                 <div id="product_list" class="list-group mt-2"></div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-12">
                                 <label class="form-label">Item Penjualan: <span class="text-danger">*</span></label>
                                 <div class="table-responsive">
                                    <table class="table table-striped table-bordered dataTable" style="width: 100%;">
                                       <thead>
                                          <tr role="row">
                                             <th>Produk</th>
                                             <th>Harga</th>
                                             <th>Stok</th>
                                             <th>Jumlah</th>
                                             <th>Diskon</th>
                                             <th>Sub Total</th>
                                             <th>Aksi</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                    
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-6 ms-auto">
                                 <div class="card">
                                    <div class="card-body pt-7 pb-2">
                                       <div class="table-responsive">
                                          <table class="table border">
                                             <tbody>
                                                <tr>
                                                   <td class="py-3">Diskon</td>
                                                   <td class="py-3" id="displayDiscount">Rp 0</td>
                                                </tr>
                                                <tr>
                                                   <td class="py-3">Pengiriman</td>
                                                   <td class="py-3" id="shippingDisplay">Rp 0</td>
                                                </tr>
                                                <tr>
                                                   <td class="py-3 text-primary">Grand Total</td>
                                                   <td class="py-3 text-primary" id="grandTotal">Rp 0</td>
                                                   <input type="hidden" name="grand_total">
                                                </tr>      
                                                
                                             
                                                <tr>
                                                   <td class="py-3">Jumlah yang dibayarkan</td>
                                                   <td class="py-3" id="paidAmount">
                                                   {{-- Menggunakan input-group agar tombol menyatu --}}
                                                   <div class="input-group">
                                                         <input type="text" name="paid_amount" placeholder="Masukkan jumlah yang dibayarkan" class="form-control">
                                                         <div class="input-group-append">
                                                            {{-- INI TOMBOL BARUNYA --}}
                                                            <button type="button" class="btn btn-success" id="btn-lunas">Lunas</button>
                                                         </div>
                                                   </div>
                                                   </td>
                                                </tr>
                                                <!-- new add full paid functionality  -->
                                                <tr class="d-none">
                                                   <td class="py-3">Lunas</td>
                                                   <td class="py-3" id="fullPaid"> 
                                                      <input type="text" name="full_paid" id="fullPaidInput">
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="py-3">Jumlah terhutang</td>
                                                   <td class="py-3" id="dueAmount">Rp 0</td>
                                                   <input type="hidden" name="due_amount">
                                                </tr>
                                          

                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-4">
                                 <label class="form-label">Diskon: </label>
                                 <input type="number" id="inputDiscount" name="discount" class="form-control" value="0">
                              </div>
                              <div class="col-md-4">
                                 <label class="form-label">Pengiriman: </label>
                                 <input type="number" id="inputShipping" name="shipping" class="form-control" value="0">
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group w-100">
                                    <label class="form-label" for="formBasic">Status : <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control form-select">
                                       <option value="Terjual">Terjual</option>
                                       <option value="Pending">Pending</option>
                                       <option value="Dipesan">Dipesan</option>
                                    </select>
                                    @error('status')
                                       <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                 </div>
                              </div>
                           </div>

                           <div class="col-md-12 mt-2">
                              <label class="form-label">Catatan: </label>
                              <textarea class="form-control" name="note" rows="3" placeholder="Masukkan catatan"></textarea>
                           </div>
                        </div>
                     </div>
                     <div class="col-xl-12">
                        <div class="d-flex justify-content-end">
                           <button class="btn btn-primary me-3" type="submit">Simpan</button>
                           <a class="btn btn-secondary" href="{{ route('all.sale') }}">Batal</a>
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

@push('scripts')
    <script>
      var productSearchUrl = "{{ route('purchase.product.search') }}"
      $(document).ready(function() {
         // Inisialisasi Select2 pada semua elemen dengan class 'select2'
         $('.select2').select2();
      });
   </script>
@endpush
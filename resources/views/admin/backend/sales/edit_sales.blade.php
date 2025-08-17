@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-4">
         <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Penjualan</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.sale') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         

 <div class="card">
    <div class="card-body">
    <form action="{{ route('update.sale',$editData->id)}}" method="post" enctype="multipart/form-data">
       @csrf


<div class="row">
 <div class="col-xl-12">
    <div class="card">
       <div class="row">
          <div class="col-md-4 mb-3">
             <label class="form-label">Tanggal: </label>
             <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" value="{{ $editData->date }}">
             @error('date')
             <span class="text-danger">{{ $message }}</span>
             @enderror
          </div>

          <div class="col-md-4 mb-3">
             <div class="form-group w-100">
                <label class="form-label" for="formBasic">Pelanggan :</label>
                <select name="customer_id" id="customer_id" class="form-control form-select" >
                   <option value="">Pilih Pelanggan</option>
                   @foreach ($customers as $item)
                   <option value="{{ $item->id }}" {{ $editData->customer_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                   @endforeach
                </select>  
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
        <label class="form-label">Item Penjualan:</label>
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
                  <tbody id="productBody">
            @foreach ($editData->saleItems as $item)
            <tr data-id={{ $item->id  }}>
               
               <td class="d-flex align-items-center gap-2">
                     <input type="text" class="form-control" value="{{ $item->product->code }} - {{ $item->product->name }}" readonly style="max-width: 300px" >
                     <button type="button" class="btn btn-primary btn-sm edit-discount-btn" 
                     data-id="{{ $item->id }}"
                     data-name="{{ $item->product->name }}"
                     data-cost="{{ $item->net_unit_cost }}"
                     data-bs-toggle="modal" data-bs-target="#discountModal" >
                     <span class="mdi mdi-book-edit "></span> 
                     </button> 
               </td>

            <td>
               <input type="number" name="products[{{ $item->product->id }}][net_unit_cost]" class="form-control net-cost" value="{{ $item->net_unit_cost }}" style="max-width: 90px;" readonly>

            </td>
            <td>
               <input type="number" name="products[{{ $item->product->id }}][stock]" class="form-control" value="{{ $item->product->product_qty }}" style="max-width: 80px;" readonly>
            </td>

            <td>
               <div class="input-group">
                     <button class="btn btn-outline-secondary decrement-qty" type="button">−</button>
                     <input type="text" class="form-control text-center qty-input"
                        name="products[{{ $item->product->id }}][quantity]" value="{{ $item->quantity }}" min="1" max="{{ $item->stock }}"
                        data-cost="{{ $item->net_unit_cost }}" style="max-width: 50px;">
                     <button class="btn btn-outline-secondary increment-qty" type="button">+</button>
               </div>
            </td>

            <td>
               <input type="number" class="form-control discount-input"
                     name="products[{{ $item->product->id }}][discount]" value="{{ $item->discount }}" style="max-width: 100px;">
            </td>

            <td class="subtotal">{{ number_format($item->subtotal,2) }}</td>
            <input type="hidden" name="products[{{ $item->product->id }}][subtotal]" value="{{ $item->subtotal }}">

            <td><button type="button" class="btn btn-danger btn-sm remove-item" data-id="{{ $item->id }}"><span class="mdi mdi-delete-circle mdi-18px"></span></button></td> 

            </tr> 
               
            @endforeach
               
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
                      <td class="py-3" id="displayDiscount">Rp {{ $editData->discount }}</td>
                   </tr>
                   <tr>
                      <td class="py-3">Pengiriman</td>
                      <td class="py-3" id="shippingDisplay">Rp {{ $editData->shipping }}</td>
                   </tr>
                   <tr>
                      <td class="py-3 text-primary">Grand Total</td>
                      <td class="py-3 text-primary" id="grandTotal">Rp {{ $editData->grand_total }}</td>
                      <input type="hidden" name="grand_total" value="{{ $editData->grand_total }}">
                   </tr>      
                   
               
                  <tr>
                      <td class="py-3">Jumlah yang dibayarkan</td>
                      <td class="py-3" id="paidAmount">
                        <div class="input-group">
                              <input type="text" name="paid_amount" value="{{ $editData->paid_amount }}" class="form-control">
                              <div class="input-group-append">
                                 {{-- INI TOMBOL BARUNYA --}}
                                 <button type="button" class="btn btn-success" id="btn-lunas">Lunas</button>
                              </div>
                        </div>
                      </td>
                   </tr>
                   <tr>
                      <td class="py-3">Jumlah terhutang</td>
                      <td class="py-3" id="dueAmount">Rp {{ $editData->due_amount }}</td>
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
            <input type="number" id="inputDiscount" name="discount" class="form-control" value="{{ $editData->discount }}">
         </div>
         <div class="col-md-4">
            <label class="form-label">Pengiriman: </label>
            <input type="number" id="inputShipping" name="shipping" class="form-control" value="{{ $editData->shipping }}">
         </div>
         <div class="col-md-4">
            <div class="form-group w-100">
               <label class="form-label" for="formBasic">Status : <span class="text-danger">*</span></label>
               <select name="status" id="status" class="form-control form-select">
                  <option value="Terjual" {{ $editData->status == 'Terjual' ? 'selected' : '' }} >Terjual</option>
                  <option value="Pending"  {{ $editData->status == 'Pending' ? 'selected' : '' }} >Pending</option>
                  <option value="Dipesan" {{ $editData->status == 'Dipesan' ? 'selected' : '' }} >Dipesan</option>
               </select>
               @error('status')
                  <span class="text-danger">{{ $message }}</span>
               @enderror
            </div>
         </div>
      </div>

      <div class="col-md-12 mt-2">
         <label class="form-label">Catatan: </label>
         <textarea class="form-control" name="note" rows="3" placeholder="Enter Notes">{{ $editData->note }}</textarea>
      </div>
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

 
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const productBody = document.getElementById("productBody");
    
        // Update subtotal when quantity or net unit cost changes
        productBody.addEventListener("input", function (e) {
            if (e.target.classList.contains("qty-input") || e.target.classList.contains("net-cost")) {
                let row = e.target.closest("tr");
                let qty = parseFloat(row.querySelector(".qty-input").value) || 0;
                let cost = parseFloat(row.querySelector(".net-cost").value) || 0;
                let discount = parseFloat(row.querySelector(".discount-input").value) || 0;
    
                let subtotal = (qty * cost) - discount;
                row.querySelector(".subtotal").textContent = subtotal.toFixed(2);
            }
        });
                
                
             // Increment quantity
             document.querySelectorAll(".increment-qty").forEach(button => {
                button.addEventListener("click", function () {
                   let input = this.closest(".input-group").querySelector(".qty-input");
                   let max = parseInt(input.getAttribute("max"));
                   let value = parseInt(input.value);
                   if (value < max) {
                         input.value = value + 1;
                         updateSubtotal(this.closest("tr"));
                   }
                });
             });
 
             // Decrement quantity
             document.querySelectorAll(".decrement-qty").forEach(button => {
                button.addEventListener("click", function () {
                   let input = this.closest(".input-group").querySelector(".qty-input");
                   let min = parseInt(input.getAttribute("min"));
                   let value = parseInt(input.value);
                   if (value > min) {
                         input.value = value - 1;
                         updateSubtotal(this.closest("tr"));
                   }
                });
             });
 
 
          function updateSubtotal(row) {
             let qty = parseFloat(row.querySelector(".qty-input").value);
             let discount = parseFloat(row.querySelector(".discount-input").value) || 0;
             let netUnitCost = parseFloat(row.querySelector(".qty-input").dataset.cost);
 
             // Calculate subtotal after discount
             let subtotal = (netUnitCost * qty) - discount;
             
             // Update visible subtotal
             row.querySelector(".subtotal").innerText = subtotal.toFixed(2);
 
             // Update hidden input for subtotal
             row.querySelector("input[name^='products['][name$='][subtotal]']").value = subtotal.toFixed(2);
 
             // Update Grand Total
             updateGrandTotal();
          }
 
 
 
       // Grand total update function
       function updateGrandTotal() {
          let grandTotal = 0;
 
          // Calculate subtotal sum
          document.querySelectorAll(".subtotal").forEach(function (item) {
             grandTotal += parseFloat(item.textContent) || 0;
          });
 
          // Get discount and shipping values
          let discount = parseFloat(document.getElementById("inputDiscount").value) || 0;
          let shipping = parseFloat(document.getElementById("inputShipping").value) || 0;
 
          // Apply discount and add shipping cost
          grandTotal = grandTotal - discount + shipping;
 
          // Ensure grand total is not negative
          if (grandTotal < 0) {
             grandTotal = 0;
          }
 
          // Update Grand Total display
          document.getElementById("grandTotal").textContent = `Rp ${grandTotal.toFixed(2)}`;
 
          // Also update the hidden input field
          document.getElementById("grandTotalInput").value = grandTotal.toFixed(2);
       }
 
 
       // Remove item
       productBody.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-item")) {
                e.target.closest("tr").remove();
                updateGrandTotal();
            }
        });
    
    
    });
    
 </script>


@endsection
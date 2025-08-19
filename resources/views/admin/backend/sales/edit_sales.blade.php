@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid my-0">
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h2 class="fs-22 fw-semibold m-0">Edit Penjualan</h2>
                </div>
                <div class="text-end">
                    <a href="{{ route('all.sale') }}" class="btn btn-dark">Kembali</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    {{-- Form action mengarah ke route update dengan ID sale --}}
                    <form action="{{ route('update.sale', $sale->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tanggal: <span class="text-danger">*</span></label>
                                            <input type="date" name="date" value="{{ $sale->date }}" class="form-control">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <div class="form-group w-100">
                                                <label class="form-label" for="formBasic">Pelanggan : <span class="text-danger">*</span></label>
                                                <select name="customer_id" id="customer_id" class="form-control form-select select2">
                                                    <option value="">Pilih Pelanggan</option>
                                                    @foreach ($customers as $item)
                                                    <option value="{{ $item->id }}" {{ $item->id == $sale->customer_id ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Produk:</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="search" id="product_search" name="search" class="form-control" placeholder="Cari produk untuk ditambahkan">
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
                                                        {{-- PERUBAHAN UTAMA: Loop dan struktur <tr> disesuaikan dengan JavaScript --}}
                                                        @foreach($sale->saleItems as $item)
                                                        <tr data-id="{{ $item->product_id }}">
                                                            <td>
                                                                {{ $item->product->code }} - {{ $item->product->name }}
                                                                <button type="button" class="btn btn-primary btn-sm edit-discount-btn"
                                                                    data-id="{{ $item->product_id }}"
                                                                    data-name="{{ $item->product->name }}"
                                                                    data-cost="{{ $item->net_unit_cost }}"
                                                                    data-bs-toggle="modal">
                                                                    <span class="mdi mdi-book-edit"></span>
                                                                </button>
                                                                <input type="hidden" name="products[{{ $item->product_id }}][id]" value="{{ $item->product_id }}">
                                                                <input type="hidden" name="products[{{ $item->product_id }}][name]" value="{{ $item->product->name }}">
                                                                <input type="hidden" name="products[{{ $item->product_id }}][code]" value="{{ $item->product->code }}">
                                                            </td>
                                                            <td>
                                                                {{ number_format($item->net_unit_cost, 0) }}
                                                                <input type="hidden" name="products[{{ $item->product_id }}][cost]" value="{{ $item->net_unit_cost }}">
                                                            </td>
                                                            {{-- Stok saat ini + jumlah yg dibeli di transaksi ini --}}
                                                            <td style="color:#ffc121">{{ $item->product->product_qty + $item->quantity }}</td>
                                                            <td>
                                                                <div class="input-group">
                                                                    <button class="btn btn-outline-secondary decrement-qty" type="button">−</button>
                                                                    <input type="text" class="form-control text-center qty-input"
                                                                        name="products[{{ $item->product_id }}][quantity]"
                                                                        value="{{ $item->quantity }}" min="1" max="{{ $item->product->product_qty + $item->quantity }}"
                                                                        data-cost="{{ $item->net_unit_cost }}" style="width: 30px;">
                                                                    <button class="btn btn-outline-secondary increment-qty" type="button">+</button>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control discount-input"
                                                                    name="products[{{ $item->product_id }}][discount]"
                                                                    value="{{ $item->discount }}" min="0" style="width:100px">
                                                            </td>
                                                            <td class="subtotal">{{ number_format($item->subtotal, 0) }}</td>
                                                            <td><button type="button" class="btn btn-danger btn-sm remove-product"><span class="mdi mdi-delete-circle mdi-18px"></span></button></td>
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
                                                                    <td class="py-3" id="displayDiscount">Rp {{ $sale->discount }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="py-3">Pengiriman</td>
                                                                    <td class="py-3" id="shippingDisplay">Rp {{ $sale->shipping }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="py-3 text-primary">Grand Total</td>
                                                                    <td class="py-3 text-primary" id="grandTotal">Rp 0</td>
                                                                    <input type="hidden" name="grand_total">
                                                                </tr>
                                                                <tr>
                                                                    <td class="py-3">Jumlah yang dibayarkan</td>
                                                                    <td class="py-3" id="paidAmount">
                                                                        <div class="input-group">
                                                                            <input type="text" name="paid_amount" placeholder="Masukkan jumlah yang dibayarkan" class="form-control" value="{{ $sale->paid_amount }}">
                                                                            <div class="input-group-append">
                                                                                <button type="button" class="btn btn-success" id="btn-lunas">Lunas</button>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr class="d-none">
                                                                    <td class="py-3">Lunas</td>
                                                                    <td class="py-3" id="fullPaid"><input type="text" name="full_paid" id="fullPaidInput"></td>
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
                                            <label class="form-label">Diskon Keseluruhan: </label>
                                            <input type="number" id="inputDiscount" name="discount" class="form-control" value="{{ $sale->discount }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Pengiriman: </label>
                                            <input type="number" id="inputShipping" name="shipping" class="form-control" value="{{ $sale->shipping }}">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group w-100">
                                                <label class="form-label">Status : <span class="text-danger">*</span></label>
                                                <select name="status" id="status" class="form-control form-select">
                                                    <option value="Terjual" {{ $sale->status == 'Terjual' ? 'selected' : '' }}>Terjual</option>
                                                    <option value="Pending" {{ $sale->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="Dipesan" {{ $sale->status == 'Dipesan' ? 'selected' : '' }}>Dipesan</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Catatan: </label>
                                        <textarea class="form-control" name="note" rows="3">{{ $sale->note }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary me-3" type="submit">Update Penjualan</button>
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
      var productSearchUrl = "{{ route('purchase.product.search') }}";

        // PENTING: Panggil fungsi kalkulasi awal setelah halaman siap
        document.addEventListener("DOMContentLoaded", function () {
            // Panggil updateEvents() untuk memastikan baris yang ada sudah bisa di-edit (qty, diskon, hapus)
            if(typeof updateEvents === 'function') {
                updateEvents();
            }

            // Panggil updateGrandTotal() untuk menghitung total awal dari item yang ada
            if(typeof updateGrandTotal === 'function') {
                updateGrandTotal();
            }
        });
      $(document).ready(function() {
         // Inisialisasi Select2 pada semua elemen dengan class 'select2'
         $('.select2').select2();
      });
   </script>
@endpush
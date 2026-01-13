@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="content">
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit Transaksi Jasa Jahit</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('update.tailor', $transaction->id) }}" method="POST" id="tailorTransactionForm">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="work-type" class="form-label">Tipe Pengerjaan <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="work_type" id="internalRadio" value="Internal" {{ $transaction->work_type == 'Internal' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="internalRadio">Internal (Penjahit In-House)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="work_type" id="eksternalRadio" value="Eksternal" {{ $transaction->work_type == 'Eksternal' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="eksternalRadio">Eksternal</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="customer_id" class="form-label">Pelanggan <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select select2 w-100" required>
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $customer->id == $transaction->customer_id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="transaction_date" class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="transaction_date" id="transaction_date" value="{{ $transaction->transaction_date }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="due_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="due_date" id="due_date" value="{{ $transaction->due_date }}">
                                </div>
                                <div class="col-md-3" id="internal_tailor_div">
                                    <label for="tailor_id" class="form-label">Ditugaskan Kepada</label>
                                    <select name="tailor_id" id="tailor_id" class="form-select">
                                        <option value="">Pilih Penjahit</option>
                                        @foreach($tailors as $tailor)
                                        <option value="{{ $tailor->id }}" {{ $tailor->id == $transaction->tailor_id ? 'selected' : '' }}>{{ $tailor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3" id="external_tailor_div" style="display: none;">
                                    <label for="supplier_id" class="form-label">Ditugaskan Kepada (Eksternal)</label>
                                    <select name="supplier_id" id="supplier_id" class="form-select">
                                        <option value="">Pilih Penjahit Eksternal</option>
                                        @foreach($serviceSuppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ $supplier->id == $transaction->supplier_id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                     <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                     <select name="status" id="status" class="form-select">
                                         <option value="Antrian" {{ $transaction->status == 'Antrian' ? 'selected' : '' }}>Antrian</option>
                                         <option value="Dikerjakan" {{ $transaction->status == 'Dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                                         <option value="Selesai" {{ $transaction->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                         <option value="Diambil" {{ $transaction->status == 'Diambil' ? 'selected' : '' }}>Diambil</option>
                                     </select>
                                </div>
                                <div class="col-md-3" id="picked_up_at_div" style="display: none;">
                                    <label for="picked_up_at" class="form-label">Tanggal Diambil</label>
                                    <input type="datetime-local" class="form-control" name="picked_up_at" id="picked_up_at" value="{{ $transaction->picked_up_at ? \Carbon\Carbon::parse($transaction->picked_up_at)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>

                            <div class="row g-3 align-items-end p-3 my-3 bg-light rounded">
                                <div class="col-md-2">
                                    <label for="type_selector" class="form-label">Jenis Jasa</label>
                                    <select id="type_selector" class="form-select">
                                        <option value="">Pilih Jenis Jasa</option>
                                        @foreach($types as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="service_id_selector" class="form-label">Komponen</label>
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <select id="service_id_selector" class="form-select">
                                                <option value="">Pilih Komponen</option>
                                                @foreach($services as $item)
                                                <option value="{{ $item->id }}" data-price="{{ $item->base_price }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" id="manual_service_name" class="form-control" placeholder="Ketik Nama Komponen" style="display: none;">
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary ms-2" id="toggle_service_input_btn" title="Input Manual">Manual</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label for="item_quantity" class="form-label">Jumlah</label>
                                    <input type="number" class="form-control" id="item_quantity" value="1" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="item_price" class="form-label">Harga Satuan</label>
                                    <input type="number" class="form-control" id="item_price" placeholder="Harga Jasa" value="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" id="addItemBtn">Tambah Item</button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                     <label class="form-label">Item Jasa Dipesan</label>
                                     <div class="table-responsive">
                                         <table class="table table-bordered">
                                             <thead>
                                                 <tr>
                                                     <th>Jenis Jasa</th>
                                                     <th>Komponen</th>
                                                     <th>Jumlah</th>
                                                     <th width="20%">Harga Satuan</th>
                                                     <th>Subtotal</th>
                                                     <th>Aksi</th>
                                                 </tr>
                                             </thead>
                                             <tbody id="transactionItemsTbody">
                                                 @foreach($transaction->items as $item)
                                                 <tr class="transaction-item">
                                                     <td>
                                                         <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                                         <input type="hidden" name="items[{{ $item->id }}][service_type_id]" value="{{ $item->service_type_id }}">
                                                         @if($item->service_id)
                                                             <input type="hidden" name="items[{{ $item->id }}][service_id]" value="{{ $item->service_id }}">
                                                         @else
                                                             <input type="hidden" name="items[{{ $item->id }}][manual_service_name]" value="{{ $item->nama_komponen }}">
                                                         @endif
                                                         <input type="hidden" class="item-quantity" name="items[{{ $item->id }}][quantity]" value="{{ $item->quantity }}">
                                                         <input type="hidden" class="item-subtotal">
                                                         {{ $item->type->name ?? 'Tipe Dihapus' }}
                                                     </td>
                                                     <td>{{ $item->nama_komponen }}</td>
                                                     <td>{{ $item->quantity }}</td>
                                                     <td>
                                                         <input type="number" name="items[{{ $item->id }}][price]" class="form-control item-price" value="{{ $item->price }}">
                                                     </td>
                                                     <td class="subtotal-display">@rupiah($item->subtotal)</td>
                                                     <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Hapus</button></td>
                                                 </tr>
                                                 @endforeach
                                             </tbody>
                                         </table>
                                     </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-outline-info waves-effect waves-light w-100" id="toggle-product-form-btn">
                                        <i class="ri-add-circle-line align-middle me-1"></i> 
                                        Tambah / Edit Produk dari Stok Toko (Opsional)
                                    </button>
                                </div>
                            </div>

                            <div id="product-form-container" style="display: none;">
                                 {{-- Form Pencarian Produk --}}
                                <div class="row g-3 align-items-end pb-3 px-3 my-3 bg-light rounded">
                                    <div class="col-md-8">
                                        <label for="product_search_input" class="form-label">Cari & Tambah Produk dari Stok</label>
                                        <input type="search" id="product_search_input" class="form-control" placeholder="Ketik nama atau kode produk...">
                                        <div id="product_list" class="list-group" style="position: absolute; z-index: 1000; width: 63%;"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="product_quantity_input" class="form-label">Jumlah</label>
                                        <input type="number" class="form-control" id="product_quantity_input" value="1" min="1">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-info w-100" id="addProductBtn">Tambah Produk</button>
                                    </div>
                                </div>

                                {{-- Tabel untuk produk yang sudah ditambahkan --}}
                                <div class="row">
                                    <div class="col-12">
                                            <label class="form-label">Produk dari Stok yang Digunakan</label>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Produk</th>
                                                            <th>Jumlah</th>
                                                            <th>Harga Satuan</th>
                                                            <th>Subtotal</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="soldProductsTbody">
                                                        {{-- Loop untuk menampilkan produk yang sudah ada di transaksi ini --}}
                                                        @foreach($transaction->soldProducts as $soldProduct)
                                                        <tr class="sold-product-item" data-id="{{ $soldProduct->product->id }}">
                                                            <td>
                                                                <input type="hidden" name="products[{{ $soldProduct->product->id }}][id]" value="{{ $soldProduct->product->id }}">
                                                                <input type="hidden" name="products[{{ $soldProduct->product->id }}][price]" value="{{ $soldProduct->price }}">
                                                                {{ $soldProduct->product->name }}
                                                            </td>
                                                            <td>
                                                                <input type="hidden" name="products[{{ $soldProduct->product->id }}][quantity]" value="{{ $soldProduct->quantity }}">
                                                                {{ $soldProduct->quantity }}
                                                            </td>
                                                            <td>@rupiah($soldProduct->price)</td>
                                                            <td class="product-subtotal">@rupiah($soldProduct->price * $soldProduct->quantity)</td>
                                                            <td><button type="button" class="btn btn-sm btn-danger removeProductBtn">Hapus</button></td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                     <label for="description" class="form-label">Deskripsi / Catatan</label>
                                     <textarea name="description" id="description" class="form-control" rows="4">{{ $transaction->description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td>Total Harga</td>
                                                <td class="text-end fw-bold fs-18" id="display_total_price">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td id="cost_price_label">Biaya Modal</td>
                                                <td><input type="number" name="cost_price" id="cost_price" class="form-control" value="{{ $transaction->cost_price }}"></td>
                                            </tr>
                                            <tr>
                                                <td>Profit</td>
                                                <td class="text-end fw-bold fs-18" id="display_profit">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td>Jumlah Dibayar</td>
                                                <td id="paidAmount">
                                                    <div class="input-group">
                                                        <input type="text" name="paid_amount" placeholder="Masukkan jumlah yang dibayarkan" class="form-control" value="{{ $transaction->paid_amount }}">
                                                        <div class="input-group-append">
                                                            {{-- INI TOMBOL BARUNYA --}}
                                                            <button type="button" class="btn btn-success" id="btn-lunas">Lunas</button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Sisa Bayar</td>
                                                <td class="text-end fw-bold fs-18 text-danger" id="display_due_amount">Rp 0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">Update Transaksi</button>
                                    <a href="{{ route('all.tailor') }}" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>

                        </form>
                    </div> </div> </div> </div>

    </div> </div> 

@endsection

@push('scripts')
    <script type="text/javascript">
    $(document).ready(function(){
        $('.select2').select2();

        let isManualServiceInput = false;

        $('#toggle_service_input_btn').on('click', function() {
            isManualServiceInput = !isManualServiceInput;
            if (isManualServiceInput) {
                $('#service_id_selector').hide();
                $('#manual_service_name').show().focus();
                $(this).text('Pilih').attr('title', 'Pilih dari Daftar');
                $('#item_price').val('0').prop('readonly', false);
            } else {
                $('#manual_service_name').hide();
                $('#service_id_selector').show();
                $(this).text('Manual').attr('title', 'Input Manual');
                $('#service_id_selector').val(null).trigger('change');
            }
        });

        $('#service_id_selector').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = selectedOption.data('price') || '0';
            $('#item_price').val(price);
        });

        $('#addItemBtn').on('click', function() {
        let itemCounter = Date.now(); // Gunakan timestamp untuk ID unik item baru
        const typeSelector = $('#type_selector');
        const typeId = typeSelector.val();
        const typeName = typeSelector.find('option:selected').text();
        if (!typeId) {
            alert('Silakan pilih "Jenis Jasa" terlebih dahulu!');
            return;
        }
        let serviceId = '';
        let serviceName = '';
        if (isManualServiceInput) {
            serviceName = $('#manual_service_name').val();
            if (!serviceName) {
                alert('Nama komponen jahit manual harus diisi.');
                return;
            }
        } else {
            serviceId = $('#service_id_selector').val();
            serviceName = $('#service_id_selector').find('option:selected').text();
            if (!serviceId) {
                alert('Silakan pilih komponen jahit dari daftar.');
                return;
            }
        }
        const quantity = parseInt($('#item_quantity').val());
        const price = parseFloat($('#item_price').val()) || 0;
        if (isNaN(quantity) || quantity <= 0) {
            alert('Jumlah harus diisi dengan angka yang valid.');
            return;
        }
        const subtotal = quantity * price;
        const hiddenServiceInput = isManualServiceInput ?
            `<input type="hidden" name="items[${itemCounter}][manual_service_name]" value="${serviceName}">` :
            `<input type="hidden" name="items[${itemCounter}][service_id]" value="${serviceId}">`;
        
        // **PERBAIKAN PADA BARIS BARU**
        const newRow = `
            <tr class="transaction-item">
                <td>
                    <input type="hidden" name="items[${itemCounter}][id]" value=""> {{-- ID KOSONG TANDA ITEM BARU --}}
                    <input type="hidden" name="items[${itemCounter}][service_type_id]" value="${typeId}">
                    ${hiddenServiceInput}
                    <input type="hidden" class="item-quantity" name="items[${itemCounter}][quantity]" value="${quantity}">
                    <input type="hidden" class="item-subtotal" value="${subtotal}">
                    ${typeName}
                </td>
                <td>${serviceName}</td>
                <td>${quantity}</td>
                <td>
                    <input type="number" name="items[${itemCounter}][price]" class="form-control item-price" value="${price}">
                </td>
                <td class="subtotal-display">${formatRupiah(subtotal)}</td>
                <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Hapus</button></td>
            </tr>
        `;

        $('#transactionItemsTbody').append(newRow);
        updateTotals();
        
        // Reset form penambahan
        typeSelector.val(null).trigger('change');
        $('#service_id_selector').val(null).trigger('change');
        $('#manual_service_name').val('');
        $('#item_quantity').val(1);
        $('#item_price').val(0);
    });

    // ============================================================
    // ## KODE PERBAIKAN UTAMA ADA DI SINI ##
    // ============================================================

    // 1. Pemicu saat harga item yang sudah ada atau yang baru diubah
    $(document).on('input', '.item-price', function() {
        updateTotals();
    });

    // 2. Pemicu saat jumlah bayar atau biaya modal diubah
    $('input[name="cost_price"], input[name="paid_amount"]').on('input', function() {
        updateTotals();
    });

    // 3. Tombol Lunas
    $('#btn-lunas').on('click', function() {
        let currentTotalPrice = 0;
        $('#transactionItemsTbody tr.transaction-item').each(function() {
             const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
             const price = parseFloat($(this).find('.item-price').val()) || 0;
             currentTotalPrice += quantity * price;
        });
        $('input[name="paid_amount"]').val(currentTotalPrice).trigger('input'); // Trigger input agar updateTotals terpanggil
    });
    
    // 4. Tombol Hapus Item
    $(document).on('click', '.removeItemBtn', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    // Toggle Form Produk
    $('#toggle-product-form-btn').on('click', function() {
        $('#product-form-container').slideToggle();
        if ($('#product-form-container').is(":visible")) {
            $(this).html('<i class="ri-subtract-line align-middle me-1"></i> Sembunyikan Form Produk');
        } else {
            $(this).html('<i class="ri-add-circle-line align-middle me-1"></i> Tambah / Edit Produk dari Stok Toko (Opsional)');
        }
    });

    // Pencarian Produk
    const productSearchInput = document.getElementById("product_search_input");
    const productList = document.getElementById("product_list");
    let selectedProduct = null;

    productSearchInput.addEventListener("keyup", function () {
        const query = this.value;
        if (query.length > 1) {
            fetchProducts(query);
        } else {
            productList.innerHTML = "";
        }
    });

    function fetchProducts(query) {
        const url = "{{ route('purchase.product.search') }}" + "?query=" + query; 
        fetch(url)
            .then(response => response.json())
            .then(data => {
                productList.innerHTML = "";
                if (data.length > 0) {
                    data.forEach(product => {
                        const itemHTML = `
                            <a href="#" class="list-group-item list-group-item-action product-item"
                                data-id="${product.id}" data-name="${product.name}"
                                data-price="${product.price}" data-stock="${product.product_qty}">
                                ${product.code} - ${product.name} (Stok: ${product.product_qty})
                            </a>`;
                        productList.innerHTML += itemHTML;
                    });
                } else {
                    productList.innerHTML = '<p class="list-group-item text-muted">Produk tidak ditemukan</p>';
                }
            });
    }

    document.addEventListener("click", function(e) {
        if (e.target && e.target.classList.contains("product-item")) {
            e.preventDefault();
            selectedProduct = {
                id: e.target.dataset.id, name: e.target.dataset.name,
                price: e.target.dataset.price, stock: e.target.dataset.stock,
                text: e.target.textContent.trim()
            };
            productSearchInput.value = selectedProduct.text;
            productList.innerHTML = "";
        }
    });

    // Tombol Tambah Produk ke Tabel
    $('#addProductBtn').on('click', function() {
        if (!selectedProduct) {
            alert('Silakan cari dan pilih produk terlebih dahulu.');
            return;
        }
        const quantity = parseInt($('#product_quantity_input').val());
        if (isNaN(quantity) || quantity <= 0) {
            alert('Jumlah harus valid.');
            return;
        }
        if (quantity > selectedProduct.stock) {
            alert('Stok tidak mencukupi! Stok tersisa: ' + selectedProduct.stock);
            return;
        }
        const price = parseFloat(selectedProduct.price);
        const subtotal = quantity * price;
        const newRow = `
            <tr class="sold-product-item" data-id="${selectedProduct.id}">
                <td>
                    <input type="hidden" name="products[${selectedProduct.id}][id]" value="${selectedProduct.id}">
                    <input type="hidden" name="products[${selectedProduct.id}][price]" value="${price}">
                    ${selectedProduct.name}
                </td>
                <td>
                    <input type="hidden" name="products[${selectedProduct.id}][quantity]" value="${quantity}">
                    ${quantity}
                </td>
                <td>${formatRupiah(price)}</td>
                <td class="product-subtotal">${formatRupiah(subtotal)}</td>
                <td><button type="button" class="btn btn-sm btn-danger removeProductBtn">Hapus</button></td>
            </tr>
        `;
        $('#soldProductsTbody').append(newRow);
        updateTotals();
        productSearchInput.value = "";
        $('#product_quantity_input').val(1);
        selectedProduct = null;
    });

    // Tombol Hapus Produk dari Tabel
    $(document).on('click', '.removeProductBtn', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    $(document).on('input', '.item-price', function() { updateTotals(); });
    $('input[name="cost_price"], input[name="paid_amount"]').on('input', function() { updateTotals(); });

    // 2. Tombol Lunas (Logika diperbarui untuk mencakup produk)
    $('#btn-lunas').on('click', function() {
        let serviceTotalPrice = 0;
        $('#transactionItemsTbody tr').each(function() {
            const quantity = parseFloat($(this).find('input[name*="[quantity]"]').val()) || 0;
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            serviceTotalPrice += quantity * price;
        });

        let productTotalPrice = 0;
        $('#soldProductsTbody .sold-product-item').each(function() {
            let price = parseFloat($(this).find('input[name*="[price]"]').val()) || 0;
            let qty = parseInt($(this).find('input[name*="[quantity]"]').val()) || 0;
            productTotalPrice += price * qty;
        });

        let grandTotal = serviceTotalPrice + productTotalPrice;
        $('input[name="paid_amount"]').val(grandTotal).trigger('input');
    });
    
    // 3. Fungsi Kalkulasi Total (Logika diperbarui untuk mencakup produk)
    function updateTotals() {
        let serviceTotalPrice = 0;
        $('#transactionItemsTbody tr').each(function() {
            const row = $(this);
            const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const subtotal = quantity * price;
            row.find('.item-subtotal').val(subtotal);
            row.find('.subtotal-display').text(formatRupiah(subtotal));
            serviceTotalPrice += subtotal;
        });

        let productTotalPrice = 0;
        $('#soldProductsTbody .sold-product-item').each(function() {
            const row = $(this);
            const price = parseFloat(row.find('input[name*="[price]"]').val()) || 0;
            const quantity = parseInt(row.find('input[name*="[quantity]"]').val()) || 0;
            const subtotal = price * quantity;
            row.find('.product-subtotal').text(formatRupiah(subtotal));
            productTotalPrice += subtotal;
        });

        const totalPrice = serviceTotalPrice + productTotalPrice;
        const costPrice = parseFloat($('input[name="cost_price"]').val()) || 0;
        const paidAmount = parseFloat($('input[name="paid_amount"]').val()) || 0;
        const profit = serviceTotalPrice - costPrice; // Profit hanya dari jasa
        const dueAmount = totalPrice - paidAmount;

        $('#display_total_price').text(formatRupiah(totalPrice));
        $('#display_profit').text(formatRupiah(profit));
        $('#display_due_amount').text(formatRupiah(dueAmount));
    }

    // 4. Fungsi format Rupiah (tetap sama)
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }
    
    // 5. Logika Toggle Penjahit (tetap sama)
    // ... (salin kode toggleTailorSelection Anda yang sudah ada di sini jika ada)
    
    function togglePickedUpDate() {
        if ($('#status').val() === 'Diambil') {
            $('#picked_up_at_div').show();
        } else {
            $('#picked_up_at_div').hide();
        }
    }

    $('#status').on('change', function() {
        if ($(this).val() === 'Diambil') {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const formatted = now.toISOString().slice(0, 16);

            if (!$('#picked_up_at').val()) {
                $('#picked_up_at').val(formatted);
            }
        }
        togglePickedUpDate();
    });

    // Run on load
    togglePickedUpDate();

    // ## PENTING: Panggil updateTotals() sekali saat halaman dimuat ##
    updateTotals(); 
});
</script>
@endpush
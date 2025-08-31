@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="content">
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Tambah Transaksi Jasa Jahit</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('store.tailor') }}" method="POST" id="tailorTransactionForm">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="work-type" class="form-label">Tipe Pengerjaan <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="work_type" id="internalRadio" value="Internal" checked>
                                            <label class="form-check-label" for="internalRadio">Internal (Penjahit In-House)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="work_type" id="eksternalRadio" value="Eksternal">
                                            <label class="form-check-label" for="eksternalRadio">Eksternal</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="customer_id" class="form-label">Pelanggan <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select select2 w-100" required>
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="transaction_date" class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="transaction_date" id="transaction_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="due_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="due_date" id="due_date">
                                </div>
                                <div class="col-md-3" id="internal_tailor_div">
                                    <label for="tailor_id" class="form-label">Ditugaskan Kepada <span class="text-danger">*</span></label>
                                    <select name="tailor_id" id="tailor_id" class="form-select" required>
                                        <option value="">Pilih Penjahit</option>
                                        @foreach($tailors as $tailor)
                                        <option value="{{ $tailor->id }}">{{ $tailor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3" id="external_tailor_div" style="display: none;">
                                    <label for="supplier_id" class="form-label">Ditugaskan Kepada (Eksternal) <span class="text-danger">*</span></label>
                                    <select name="supplier_id" id="supplier_id" class="form-select">
                                        <option value="">Pilih Penjahit Eksternal</option>
                                        @foreach($serviceSuppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                     <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                     <select name="status" id="status" class="form-select">
                                         <option value="Antrian">Antrian</option>
                                         <option value="Dikerjakan">Dikerjakan</option>
                                         <option value="Selesai">Selesai</option>
                                         <option value="Diambil">Diambil</option>
                                     </select>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end p-3 my-3 bg-light rounded">
                                <div class="col-md-2">
                                    <label for="type_selector" class="form-label">Jenis Jasa <span class="text-danger">*</span></label>
                                    <select id="type_selector" class="form-select">
                                        <option value="">Pilih Jenis Jasa</option>
                                        @foreach($types as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="service_id_selector" class="form-label">Komponen <span class="text-danger">*</span></label>
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <select id="service_id_selector" class="form-select">
                                                <option value="">Pilih Komponen</option>
                                                @foreach($services as $item)
                                                <option value="{{ $item->id }}" data-price="{{ $item->base_price }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            {{-- Input manual, awalnya disembunyikan --}}
                                            <input type="text" id="manual_service_name" class="form-control" placeholder="Ketik Nama Komponen" style="display: none;">
                                        </div>
                                        {{-- Tombol untuk beralih antara select dan input manual --}}
                                        <button type="button" class="btn btn-outline-secondary ms-2" id="toggle_service_input_btn" title="Input Manual">
                                            Manual
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label for="item_quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="item_quantity" value="1" min="1">
                                </div>
                                <div class="col-md-2">
                                    <label for="item_price" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="item_price" placeholder="Harga Jasa">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" id="addItemBtn">Tambah Item</button>
                                </div>
                            </div>

                            {{-- Tabel Item yang Dipesan --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nama Jasa</th>
                                                    <th>Jenis Komponen</th>
                                                    <th>Jumlah</th>
                                                    <th>Harga Satuan</th>
                                                    <th>Subtotal</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="transactionItemsTbody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                     <label for="description" class="form-label">Deskripsi / Catatan</label>
                                     <textarea name="description" id="description" class="form-control" rows="4"></textarea>
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
                                                <td><input type="number" name="cost_price" id="cost_price" class="form-control" value="0"></td>
                                            </tr>
                                            <tr>
                                                <td>Profit</td>
                                                <td class="text-end fw-bold fs-18" id="display_profit">Rp 0</td>
                                            </tr>
                                             <tr>
                                                <td>Jumlah Dibayar</td>
                                                <td id="paidAmount">
                                                    <div class="input-group">
                                                        <input type="text" name="paid_amount" placeholder="Masukkan jumlah yang dibayarkan" class="form-control">
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
                                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                                    <a href="{{ route('all.tailor') }}" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>

                        </form>
                    </div> </div> </div> </div>

    </div> </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 pada semua elemen dengan class 'select2'
            $('.select2').select2();

            let isManualServiceInput = false;
    let itemCounter = 0;

    $('#toggle_service_input_btn').on('click', function() {
        isManualServiceInput = !isManualServiceInput;
        
        if (isManualServiceInput) {
            $('#service_id_selector').hide();
            $('#manual_service_name').show().focus();
            $(this).html('Pilih Daftar').attr('title', 'Pilih dari Daftar');
            $('#item_price').val('').prop('readonly', false);
        } else {
            $('#manual_service_name').hide();
            $('#service_id_selector').show();
            $(this).html('Manual').attr('title', 'Input Manual');
            $('#service_id_selector').val(null).trigger('change');
        }
    });

    $('#service_id_selector').on('change', function(){
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price') || '';
        $('#item_price').val(price);
    });

    $('#addItemBtn').on('click', function(){
        itemCounter++;
        
        const typeSelector = $('#type_selector');
        const typeId = typeSelector.val();
        const typeName = typeSelector.find('option:selected').text();

        let serviceId = '';
        let serviceName = '';

        if (!typeId) {
            alert('Silakan pilih "Jenis Jasa" terlebih dahulu!');
            return; 
        }
        
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
        const price = parseFloat($('#item_price').val());

        if (isNaN(quantity) || quantity <= 0 || isNaN(price)) {
            alert('Jumlah dan harga satuan harus diisi dengan angka yang valid.');
            return;
        }

        const subtotal = quantity * price;
        const hiddenServiceInput = isManualServiceInput 
            ? `<input type="hidden" name="items[${itemCounter}][manual_service_name]" value="${serviceName}">`
            : `<input type="hidden" name="items[${itemCounter}][service_id]" value="${serviceId}">`;
        
        const newRow = `
            <tr class="transaction-item">
                <td>
                    <input type="hidden" name="items[${itemCounter}][service_type_id]" value="${typeId}">
                    ${hiddenServiceInput}
                    <input type="hidden" name="items[${itemCounter}][quantity]" value="${quantity}">
                    <input type="hidden" name="items[${itemCounter}][price]" value="${price}">
                    <input type="hidden" class="item-subtotal" value="${subtotal}">
                    ${typeName}
                </td>
                <td>${serviceName}</td>
                <td>${quantity}</td>
                <td>${formatRupiah(price)}</td>
                <td>${formatRupiah(subtotal)}</td>
                <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Hapus</button></td>
            </tr>
        `;

        $('#transactionItemsTbody').append(newRow);
        updateTotals();

        if(isManualServiceInput){
            $('#manual_service_name').val('');
        } else {
            $('#service_id_selector').val(null).trigger('change');
        }
        $('#item_quantity').val(1);
        $('#item_price').val('');
    });

    $(document).on('click', '.removeItemBtn', function(){
        $(this).closest('tr').remove();
        updateTotals();
    });

    $('input[name="cost_price"], input[name="paid_amount"]').on('input', function() {
        updateTotals();
    });

    // =======================================================
    // ## INI KODE BARU UNTUK TOMBOL LUNAS ##
    $('#btn-lunas').on('click', function() {
    // 1. Hitung total harga saat ini
    let currentTotalPrice = 0;
    $('.item-subtotal').each(function() {
    currentTotalPrice += parseFloat($(this).val()) || 0;
    });

    // 2. Masukkan total harga ke dalam input 'paid_amount'
    $('input[name="paid_amount"]').val(currentTotalPrice);

    // 3. Panggil fungsi updateTotals() agar "Sisa Bayar" ikut ter-update
    updateTotals();
    });

    function updateTotals(){
let totalPrice = 0;
$('.item-subtotal').each(function(){
totalPrice += parseFloat($(this).val()) || 0;
});

const costPrice = parseFloat($('input[name="cost_price"]').val()) || 0;
const paidAmount = parseFloat($('input[name="paid_amount"]').val()) || 0;
const profit = totalPrice - costPrice;
const dueAmount = totalPrice - paidAmount;

$('#display_total_price').text(formatRupiah(totalPrice));
$('#display_profit').text(formatRupiah(profit));
$('#display_due_amount').text(formatRupiah(dueAmount));
}

function formatRupiah(angka) {
return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

// --- Logika untuk Tipe Pengerjaan (Internal/Eksternal) ---
const workTypeRadios = document.querySelectorAll('input[name="work_type"]');
const internalTailorDiv = document.getElementById('internal_tailor_div');
const internalTailorSelect = document.getElementById('tailor_id');
const externalTailorDiv = document.getElementById('external_tailor_div');
const externalTailorSelect = document.getElementById('supplier_id');
const costPriceRow = document.getElementById('cost_price_row');
const profitRow = document.getElementById('profit_row');

function toggleTailorSelection() {
const selectedType = document.querySelector('input[name="work_type"]:checked').value;

if (selectedType === 'Internal') {
internalTailorDiv.style.display = 'block';
internalTailorSelect.required = true;
externalTailorDiv.style.display = 'none';
externalTailorSelect.required = false;

costPriceRow.style.display = 'none';
profitRow.style.display = 'none';
} else { // Eksternal
internalTailorDiv.style.display = 'none';
internalTailorSelect.required = false;
externalTailorDiv.style.display = 'block';
externalTailorSelect.required = true;

costPriceRow.style.display = 'table-row';
profitRow.style.display = 'table-row';
}
$('#tailor_id, #supplier_id').val(null).trigger('change');
}

workTypeRadios.forEach(radio => {
radio.addEventListener('change', toggleTailorSelection);
});

toggleTailorSelection();
});
    </script>
@endpush
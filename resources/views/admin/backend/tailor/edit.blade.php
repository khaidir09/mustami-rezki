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

    // 5. Fungsi Kalkulasi Total yang Diperbaiki
    function updateTotals() {
        let totalPrice = 0;
        
        // Loop melalui setiap baris item yang ada di tabel
        $('#transactionItemsTbody tr').each(function() {
            const row = $(this);
            const quantity = parseFloat(row.find('input[name*="[quantity]"]').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const subtotal = quantity * price;

            // Update nilai hidden subtotal dan tampilan subtotal
            row.find('.item-subtotal').val(subtotal);
            row.find('.subtotal-display').text(formatRupiah(subtotal));

            totalPrice += subtotal;
        });

        const costPrice = parseFloat($('input[name="cost_price"]').val()) || 0;
        const paidAmount = parseFloat($('input[name="paid_amount"]').val()) || 0;
        const profit = totalPrice - costPrice;
        const dueAmount = totalPrice - paidAmount;

        $('#display_total_price').text(formatRupiah(totalPrice));
        $('#display_profit').text(formatRupiah(profit));
        $('#display_due_amount').text(formatRupiah(dueAmount));
    }

    // 6. Fungsi format Rupiah (tetap sama)
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }
    
    // 7. Logika Toggle Penjahit (tetap sama)
    const workTypeRadios = document.querySelectorAll('input[name="work_type"]');
    // ... (salin sisa kode toggleTailorSelection Anda di sini)
    
    // ## PENTING: Panggil updateTotals() sekali di awal saat halaman dimuat ##
    updateTotals(); 
});
</script>
@endpush
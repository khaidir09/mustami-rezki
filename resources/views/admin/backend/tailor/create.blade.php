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
                                <div class="col-md-4 col-lg-2">
                                    <label for="transaction_date" class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="transaction_date" id="transaction_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                    <label for="due_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="due_date" id="due_date">
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label for="customer_id" class="form-label">Pelanggan <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select select2" required>
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label for="tailor_id" class="form-label">Ditugaskan Kepada <span class="text-danger">*</span></label>
                                    <select name="tailor_id" id="tailor_id" class="form-select" required>
                                        <option value="">Pilih Penjahit</option>
                                        @foreach($tailors as $tailor)
                                        <option value="{{ $tailor->id }}">{{ $tailor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-2">
                                     <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                     <select name="status" id="status" class="form-select">
                                         <option value="Antrian">Antrian</option>
                                         <option value="Dikerjakan">Dikerjakan</option>
                                         <option value="Selesai">Selesai</option>
                                         <option value="Diambil">Diambil</option>
                                     </select>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end px-3 pb-3 my-3 bg-light rounded">
                                <div class="col-md-5">
                                    <label for="service_id_selector" class="form-label">Pilih Jasa <span class="text-danger">*</span></label>
                                    <select id="service_id_selector" class="form-select select2">
                                        <option value="">Pilih Jasa</option>
                                        @foreach($services as $service)
                                        <option value="{{ $service->id }}" data-price="{{ $service->base_price }}">{{ $service->name }} ({{ $service->type }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="item_quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="item_quantity" value="1" min="1">
                                </div>
                                <div class="col-md-3">
                                    <label for="item_price" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="item_price" placeholder="Harga Jasa">
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
                                                    <th>Nama Jasa</th>
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
                                                <td>Biaya Modal</td>
                                                <td><input type="number" name="cost_price" id="cost_price" class="form-control" value="0"></td>
                                            </tr>
                                            <tr>
                                                <td>Profit</td>
                                                <td class="text-end fw-bold fs-18" id="display_profit">Rp 0</td>
                                            </tr>
                                             <tr>
                                                <td>Jumlah Dibayar</td>
                                                <td><input type="number" name="paid_amount" id="paid_amount" class="form-control" value="0"></td>
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

            // Saat service dipilih, otomatis isi harga satuannya
            $('#service_id_selector').on('change', function(){
                var selectedOption = $(this).find('option:selected');
                var price = selectedOption.data('price');
                $('#item_price').val(price);
            });

            // Saat tombol "Tambah Item" diklik
            $('#addItemBtn').on('click', function(){
                var serviceSelector = $('#service_id_selector');
                var serviceId = serviceSelector.val();
                var serviceName = serviceSelector.find('option:selected').text();
                var quantity = parseInt($('#item_quantity').val());
                var price = parseFloat($('#item_price').val());

                if (!serviceId || !quantity || isNaN(price)) {
                    alert('Silakan pilih jasa dan isi harga dengan benar.');
                    return;
                }

                var subtotal = quantity * price;

                var newRow = `
                    <tr>
                        <td>
                            <input type="hidden" name="items[${Date.now()}][service_id]" value="${serviceId}">
                            ${serviceName}
                        </td>
                        <td>
                            <input type="hidden" name="items[${Date.now()}][quantity]" value="${quantity}">
                            ${quantity}
                        </td>
                        <td>
                            <input type="hidden" name="items[${Date.now()}][price]" value="${price}">
                            ${formatRupiah(price)}
                        </td>
                        <td>
                            <input type="hidden" name="items[${Date.now()}][subtotal]" class="item-subtotal" value="${subtotal}">
                            ${formatRupiah(subtotal)}
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Hapus</button></td>
                    </tr>
                `;

                $('#transactionItemsTbody').append(newRow);
                updateTotals();

                // Reset input fields
                serviceSelector.val('');
                $('#item_quantity').val(1);
                $('#item_price').val('');
            });

            // Saat tombol "Hapus" pada item diklik
            $(document).on('click', '.removeItemBtn', function(){
                $(this).closest('tr').remove();
                updateTotals();
            });

            // Saat input biaya modal atau pembayaran berubah
            $('#cost_price, #paid_amount').on('input', function(){
                updateTotals();
            });

            function updateTotals(){
                var totalPrice = 0;
                $('.item-subtotal').each(function(){
                    totalPrice += parseFloat($(this).val());
                });

                var costPrice = parseFloat($('#cost_price').val()) || 0;
                var paidAmount = parseFloat($('#paid_amount').val()) || 0;

                var profit = totalPrice - costPrice;
                var dueAmount = totalPrice - paidAmount;

                $('#display_total_price').text(formatRupiah(totalPrice));
                $('#display_profit').text(formatRupiah(profit));
                $('#display_due_amount').text(formatRupiah(dueAmount));
            }

            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
            }
        });
    </script>
@endpush
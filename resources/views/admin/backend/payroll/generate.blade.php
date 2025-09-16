@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<div class="content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid my-0">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Bayar Gaji/Komisi Mingguan</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2">
                     <a href="{{ route('payroll.history') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Filter Periode Penggajian</h4>
                        <p class="card-title-desc">Pilih karyawan dan rentang tanggal untuk menghitung total gaji dan komisi yang belum dibayar.</p>
                        
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="employee_id" class="form-label">Pilih Karyawan</label>
                                <select id="employee_id" class="form-select">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date">
                            </div>

                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date">
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" type="button" id="calculate-btn">
                                    Tampilkan Rincian
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results Section --}}
        <div id="payroll-details-container" class="row" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Pratinjau Slip Gaji</h4>
                        <p class="card-title-desc">Berikut adalah rincian pendapatan untuk <strong id="employee_name_display"></strong> pada periode <strong id="period_display"></strong>.</p>
                        
                        <form action="{{ route('payroll.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="user_id" id="form_user_id">
                            <input type="hidden" name="start_date" id="form_start_date">
                            <input type="hidden" name="end_date" id="form_end_date">
                            <input type="hidden" name="grand_total" id="form_grand_total">

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Jenis Pendapatan</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Gaji Harian</td>
                                            <td class="text-end" id="total_daily_salary_display">Rp 0</td>
                                        </tr>
                                        <tr>
                                            <td>Bagi Hasil Jahit</td>
                                            <td class="text-end" id="total_tailor_commission_display">Rp 0</td>
                                        </tr>
                                        <tr>
                                            <td>Bagi Hasil Produksi Kancing</td>
                                            <td class="text-end" id="total_button_commission_display">Rp 0</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <th class="fs-5">TOTAL PERHITUNGAN</th>
                                            <th class="text-end fs-5" id="grand_total_display">Rp 0</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="final_payment_amount" class="form-label">
                                                    <strong>JUMLAH DIBAYARKAN</strong>
                                                </label>
                                            </td>
                                            <td class="text-end">
                                                <input type="number" class="form-control text-end" name="final_payment_amount" id="final_payment_amount">
                                            </td>
                                        </tr>
                                        <tr style="background-color: #e6f7ff;">
                                            <td>Bonus (Pembulatan)</td>
                                            <td class="text-end fw-bold" id="bonus_display">Rp 0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="mt-2 text-end">
                                <button class="btn btn-success" type="submit">Proses & Bayar Gaji Ini</button>
                            </div>
                        </form>
                    </div> 
                </div> 
            </div> 
        </div>

    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Fungsi untuk format mata uang Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    function calculateBonus() {
        var calculatedTotal = parseFloat($('#form_grand_total').val()) || 0;
        var finalPayment = parseFloat($('#final_payment_amount').val()) || 0;
        var bonus = finalPayment - calculatedTotal;

        if (bonus < 0) {
            bonus = 0; // Bonus tidak boleh minus
        }
        $('#bonus_display').text(formatRupiah(bonus));
    }

    // Event handler saat tombol "Tampilkan Rincian" diklik
    $('#calculate-btn').on('click', function() {
        var employeeId = $('#employee_id').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        // Validasi input
        if (!employeeId || !startDate || !endDate) {
            alert('Silakan pilih karyawan dan tentukan periode tanggal dengan lengkap.');
            return;
        }

        // Tampilkan loading (opsional)
        $(this).html('Menghitung...').prop('disabled', true);

        // Kirim request AJAX ke controller
        $.ajax({
            url: "{{ route('payroll.calculate') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                user_id: employeeId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                // Tampilkan hasil di area pratinjau
                $('#total_daily_salary_display').text(formatRupiah(response.total_daily_salary));
                $('#total_tailor_commission_display').text(formatRupiah(response.total_tailor_commission));
                $('#total_button_commission_display').text(formatRupiah(response.total_button_commission));
                $('#grand_total_display').text(formatRupiah(response.grand_total));

                // Isi data untuk form submission
                $('#form_user_id').val(employeeId);
                $('#form_start_date').val(startDate);
                $('#form_end_date').val(endDate);
                $('#form_grand_total').val(response.grand_total);
                
                // Tampilkan nama dan periode
                var employeeName = $('#employee_id option:selected').text();
                $('#employee_name_display').text(employeeName);
                $('#period_display').text(startDate + ' s/d ' + endDate);

                $('#final_payment_amount').val(response.grand_total);
                calculateBonus();

                // Tampilkan kontainer hasil dan reset tombol
                $('#payroll-details-container').slideDown();
                $('#calculate-btn').html('Tampilkan Rincian').prop('disabled', false);
            },
            error: function() {
                alert('Terjadi kesalahan saat mengambil data. Silakan coba lagi.');
                $('#calculate-btn').html('Tampilkan Rincian').prop('disabled', false);
            }
        });
    });

    $(document).on('input', '#final_payment_amount', function() {
        calculateBonus();
    });

    // Sembunyikan pratinjau jika filter diubah
    $('#employee_id, #start_date, #end_date').on('change', function(){
        $('#payroll-details-container').slideUp();
    });
});
</script>
@endpush
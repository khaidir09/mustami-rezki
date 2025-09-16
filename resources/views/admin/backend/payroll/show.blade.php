@extends('admin.admin_master')
@section('admin')

<div class="content">
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Rincian Slip Gaji</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2">
                     <a href="{{ route('payroll.history') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="invoice-title">
                            <h4 class="float-end font-size-16">Periode {{ \Carbon\Carbon::parse($payroll->period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($payroll->period_end)->format('d/m/Y') }}</h4>
                            <h3>Slip Gaji</h3>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6">
                                <address>
                                    <strong>Dibayarkan Kepada:</strong><br>
                                    <strong>{{ $payroll->user->name ?? 'N/A' }}</strong>
                                </address>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <address>
                                    <strong>Tanggal Pembayaran:</strong><br>
                                    {{ \Carbon\Carbon::parse($payroll->payment_date)->translatedFormat('l, d F Y') }}<br><br>
                                </address>
                            </div>
                        </div>

                        {{-- Rincian Pendapatan --}}
                        <div class="py-2 mt-3">
                            <h3 class="font-size-15 fw-bold">Rincian Pendapatan</h3>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>Sumber Pendapatan</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Gaji Harian --}}
                                    @if($daily_salaries->count() > 0)
                                        <tr>
                                            <td>Gaji Harian ({{ $daily_salaries->count() }} hari)</td>
                                            <td class="text-end">@rupiah($daily_salaries->sum('amount'))</td>
                                        </tr>
                                    @endif

                                    {{-- Komisi Jahit --}}
                                     @if($tailor_commissions->count() > 0)
                                        <tr>
                                            <td>Bagi Hasil Jahit ({{ $tailor_commissions->count() }} transaksi)</td>
                                            <td class="text-end">@rupiah($tailor_commissions->sum('amount'))</td>
                                        </tr>
                                    @endif

                                    {{-- Komisi Kancing --}}
                                     @if($button_commissions->count() > 0)
                                        <tr>
                                            <td>Bagi Hasil Produksi Kancing ({{ $button_commissions->sum('quantity') }} pcs)</td>
                                            <td class="text-end">@rupiah($button_commissions->sum('total_commission'))</td>
                                        </tr>
                                    @endif

                                    {{-- Bonus --}}
                                     @if($bonus)
                                        <tr>
                                            <td>Bonus (Pembulatan)</td>
                                            <td class="text-end">@rupiah($bonus->amount)</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td class="fs-5 fw-bold">TOTAL DITERIMA</td>
                                        <td class="text-end fs-5 fw-bold">@rupiah($payroll->amount)</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-print-none">
                            <div class="float-end">
                                <a href="javascript:window.print()" class="btn btn-success waves-effect waves-light"><i class="fa fa-print"></i> Cetak</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
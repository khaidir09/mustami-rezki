@extends('admin.admin_master')
@section('admin')

<div class="content">
    <div class="container-xxl">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Riwayat Penggajian</h4>
            </div>

            @if (Auth::user()->hasRole('Super Admin'))
                <div class="text-end">
                    <a href="{{ route('payroll.generate.form') }}" class="btn btn-secondary">Bayar Gaji/Komisi</a>
                </div>
            @endif
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Daftar Semua Pembayaran Gaji & Komisi</h4>
                        <p class="card-title-desc">Berikut adalah rekapitulasi semua pembayaran mingguan yang telah diproses.</p>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Nama Karyawan</th>
                                        <th>Periode</th>
                                        <th class="text-end">Jumlah Dibayarkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payrolls as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->payment_date)->format('d F Y') }}</td>
                                        <td>{{ $item->user->name ?? 'Karyawan Dihapus' }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($item->period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item->period_end)->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end fw-bold">@rupiah($item->amount)</td>
                                        <td>
                                            <a title="Lihat Rincian" href="{{ route('payroll.show', $item->id) }}" class="btn btn-info btn-sm"> 
                                                <span class="mdi mdi-eye-circle mdi-18px"></span>
                                            </a>
                                            @if (Auth::user()->hasRole('Super Admin'))
                                            <a href="{{ route('payroll.destroy',$item->id) }}" class="btn btn-danger btn-sm" id="delete"><span class="mdi mdi-delete-circle  mdi-18px"></span></a>
                                            @endif
                                            {{-- Anda bisa menambahkan tombol cetak slip gaji di sini nanti --}}
                                            {{-- <a title="Cetak Slip" href="#" class="btn btn-primary btn-sm"> 
                                                <i class="ri-printer-line"></i>
                                            </a> --}}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
        $("#datatable").dataTable({
            "columnDefs": [{
                "sortable": false,
                "targets": [5]
            }],
            "order": [[0, "asc"]]
        });
    </script>
@endpush
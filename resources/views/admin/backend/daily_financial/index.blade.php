@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Arus Kas Harian</h4>
            </div>

            <div class="text-end">
                <form action="{{ route('daily.financial.create') }}" method="GET" class="d-flex align-items-center gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-light">Pilih Tanggal</span>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        <button type="submit" class="btn btn-primary">Proses Tutup Buku</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Datatables  -->
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Saldo Awal</th>
                                <th>Total Pemasukan</th>
                                <th>Total Pengeluaran</th>
                                <th>Saldo Akhir</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($summaries as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->date->translatedFormat('d F Y') }}</td>
                                        <td>Rp {{ number_format($item->opening_balance, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->total_income, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->total_expense, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->closing_balance, 0, ',', '.') }}</td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('daily.financial.print', $item->id) }}"
                                            class="btn btn-primary btn-sm" 
                                            title="Lihat & Cetak Laporan"
                                            target="_blank">
                                                <span class="mdi mdi-printer mdi-18px"></span>
                                            </a>
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

    </div> <!-- container-fluid -->

</div> <!-- content -->



@endsection

@push('scripts')
    <script>
        $("#datatable").dataTable({
            "columnDefs": [{
                "sortable": false,
                "targets": [1,2,3,4,5]
            }],
            "order": [[0, "asc"]]
        });
    </script>
@endpush
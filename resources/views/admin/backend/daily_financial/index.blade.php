@extends('admin.admin_dashboard')
@section('admin')
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Arus Kas Harian</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Riwayat Arus Kas Harian</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('daily.financial.create') }}" class="btn btn-primary">Tutup Buku Hari Ini</a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Saldo Awal</th>
                                <th>Total Pemasukan</th>
                                <th>Total Pengeluaran</th>
                                <th>Saldo Akhir</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summaries as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->date->format('d F Y') }}</td>
                                    <td>Rp {{ number_format($item->opening_balance, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->total_income, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->total_expense, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->closing_balance, 0, ',', '.') }}</td>
                                    <td>{{ $item->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Saldo Awal</th>
                                <th>Total Pemasukan</th>
                                <th>Total Pengeluaran</th>
                                <th>Saldo Akhir</th>
                                <th>Catatan</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

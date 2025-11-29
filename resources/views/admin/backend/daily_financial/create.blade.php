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
                        <li class="breadcrumb-item active" aria-current="page">Tutup Buku Hari Ini ({{ $date->format('d F Y') }})</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4">Ringkasan Arus Kas Harian</h4>
                        <form action="{{ route('daily.financial.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                            <table class="table table-bordered mb-4">
                                <tbody>
                                    <tr>
                                        <th class="bg-light">Saldo Awal Hari</th>
                                        <td class="text-end">
                                            Rp {{ number_format($openingBalance, 0, ',', '.') }}
                                            <input type="hidden" name="opening_balance" value="{{ $openingBalance }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-success">Total Pemasukan</th>
                                        <td class="text-end text-success">
                                            + Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                            <input type="hidden" name="total_income" value="{{ $totalIncome }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-danger">Total Pengeluaran</th>
                                        <td class="text-end text-danger">
                                            - Rp {{ number_format($totalExpense, 0, ',', '.') }}
                                            <input type="hidden" name="total_expense" value="{{ $totalExpense }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-dark text-white">Saldo Akhir Hari</th>
                                        <td class="text-end fw-bold bg-dark text-white">
                                            Rp {{ number_format($closingBalance, 0, ',', '.') }}
                                            <input type="hidden" name="closing_balance" value="{{ $closingBalance }}">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan (Opsional)</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Apakah Anda yakin ingin melakukan tutup buku untuk tanggal {{ $date->format('d F Y') }}? Data tidak bisa diubah setelah ditutup.')">
                                    <i class="bx bx-check-circle"></i> Lakukan Tutup Buku
                                </button>
                                <a href="{{ route('daily.financial.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

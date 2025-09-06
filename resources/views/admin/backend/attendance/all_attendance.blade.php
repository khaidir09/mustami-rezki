@extends('admin.admin_master')
@section('admin')
<div class="content">
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Presensi Kehadiran</h4>
            </div>
        </div>

        @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Tailor'))
            {{-- Bagian Presensi untuk Admin dan Tailor --}}
            <div class="row">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <div class="card text-center">
                        <div class="card-header">
                            <h4 class="card-title">Presensi Kehadiran</h4>
                            <p class="card-title-desc">Hari ini, {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                            <p class="text-muted">Silakan lakukan presensi sesuai dengan waktu kerja Anda.</p>

                            <form action="{{ route('attendances.store') }}" method="POST">
                                @csrf
                                @if(!$attendanceToday || !$attendanceToday->check_in)
                                    {{-- Tombol Check-in --}}
                                    <button type="submit" name="action" value="check_in" class="btn btn-primary btn-lg waves-effect waves-light">
                                        <i class="ri-fingerprint-line me-2"></i> Absen Masuk
                                    </button>
                                @elseif($attendanceToday->check_in && !$attendanceToday->check_out)
                                    {{-- Tombol Check-out --}}
                                    <div class="alert alert-success">
                                        Anda sudah absen masuk pada jam: <strong>{{ \Carbon\Carbon::parse($attendanceToday->check_in)->format('H:i') }}</strong>
                                    </div>
                                    <button type="submit" name="action" value="check_out" class="btn btn-danger btn-lg waves-effect waves-light">
                                        <i class="ri-logout-box-r-line me-2"></i> Absen Pulang
                                    </button>
                                @else
                                    {{-- Sudah Selesai Absen --}}
                                    <div class="alert alert-info">
                                        Terima kasih, Anda sudah menyelesaikan presensi hari ini.
                                        <p class="mb-0 mt-2">
                                            Masuk: <strong>{{ \Carbon\Carbon::parse($attendanceToday->check_in)->format('H:i') }}</strong> | 
                                            Pulang: <strong>{{ \Carbon\Carbon::parse($attendanceToday->check_out)->format('H:i') }}</strong>
                                        </p>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama</th>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendance as $key=> $item) 
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $item->user->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                                        <td>{{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}</td>
                                        <td>{{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}</td>
                                        <td>
                                            @if ($item->check_in && $item->check_out)
                                                <span class="badge text-bg-success">Hadir</span>
                                            @elseif ($item->check_in && !$item->check_out)
                                                <span class="badge text-bg-warning">Belum Pulang</span>
                                            @else
                                                <span class="badge text-bg-danger">Tidak Hadir</span>
                                            @endif
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
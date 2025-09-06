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
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="card text-center">
                        <div class="card-header">
                            <h4 class="card-title">Presensi Kehadiran</h4>
                            <p class="card-title-desc">Hari ini, {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                            <p class="text-muted">Silakan lakukan presensi sesuai dengan kondisi Anda hari ini.</p>

                            @if(!$attendanceToday)
                                <form action="{{ route('attendances.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    {{-- Tombol Check-in --}}
                                    <button type="submit" name="action" value="check_in" class="btn btn-primary waves-effect waves-light">
                                        Absen Masuk
                                    </button>
                                </form>
                                <button type="button" class="btn btn-warning waves-effect waves-light btn-absence" data-status="Izin">
                                    Ajukan Izin
                                </button>
                                <button type="button" class="btn btn-danger waves-effect waves-light btn-absence" data-status="Sakit">
                                    Lapor Sakit
                                </button>

                                {{-- <form action="{{ route('attendances.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="absence">
                                    <button type="submit" name="status" value="Izin" class="btn btn-absence btn-warning waves-effect waves-light">
                                        Ajukan Izin
                                    </button>
                                </form>
                                <form action="{{ route('attendances.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="absence">
                                    <button type="submit" name="status" value="Sakit" class="btn btn-absence btn-danger waves-effect waves-light">
                                        Lapor Sakit
                                    </button>
                                </form> --}}

                                <div id="absence-form-container" class="mt-4 text-start" style="display: none;">
                                    <form action="{{ route('attendances.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="absence">
                                        <input type="hidden" name="status" id="absence_status">
                                        
                                        <div class="mb-3">
                                            <label for="notes" class="form-label" id="absence_label">Keterangan</label>
                                            <textarea name="notes" class="form-control" rows="3" placeholder="Tuliskan keterangan Anda di sini..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Kirim</button>
                                        <button type="button" class="btn btn-secondary" id="cancel-absence">Batal</button>
                                    </form>
                                </div>
                            @else
                                {{-- Tombol Check-out --}}
                                @if ($attendanceToday->status == 'Hadir')
                                    <div class="alert alert-success">
                                        Anda sudah absen masuk pada jam: <strong>{{ \Carbon\Carbon::parse($attendanceToday->check_in)->format('H:i') }}</strong>
                                    </div>
                                @elseif ($attendanceToday->status == 'Izin')
                                    <div class="alert alert-info">
                                        Anda telah mengajukan: <strong>{{ $attendanceToday->status }}</strong> dengan keterangan: <em>{{ $attendanceToday->notes }}</em>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        Anda telah melaporkan: <strong>{{ $attendanceToday->status }}</strong> dengan keterangan: <em>{{ $attendanceToday->notes }}</em>
                                    </div>
                                @endif
                            @endif
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
                            <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama</th>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                {{-- <th>Jam Pulang</th> --}}
                                <th>Status</th>
                                @if (Auth::user()->hasRole('Super Admin'))
                                    <th>Aksi</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendance as $key=> $item) 
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $item->user->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                                        <td>{{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}</td>
                                        <td>
                                            @if ($item->status == 'Hadir')
                                                <span class="badge text-bg-success">{{ $item->status }}</span>
                                            @else
                                                <span class="badge text-bg-danger">{{ $item->status }}</span>
                                            @endif
                                        </td>
                                        @if (Auth::user()->hasRole('Super Admin'))
                                            <td><a href="{{ route('attendances.delete',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Hapus</a></td>
                                        @endif
                                        {{-- <td>{{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}</td> --}}
                                        {{-- <td>
                                            @if ($item->check_in)
                                                <span class="badge text-bg-success">Hadir</span>
                                            @else
                                                <span class="badge text-bg-danger">Tidak Hadir</span>
                                            @endif
                                        </td> --}}
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
    <script>
        $(document).ready(function() {
            // Ketika tombol .btn-absence (Izin atau Sakit) diklik
            $('.btn-absence').on('click', function() {
                // Ambil status dari atribut data-status (Izin atau Sakit)
                var status = $(this).data('status');

                // Isi input tersembunyi dengan status yang sesuai
                $('#absence_status').val(status);
                
                // Ubah label form sesuai status yang dipilih
                $('#absence_label').text('Keterangan ' + status);

                // Tampilkan form keterangan dengan animasi slide down
                $('#absence-form-container').slideDown();
            });

            // Ketika tombol Batal di dalam form keterangan diklik
            $('#cancel-absence').on('click', function() {
                // Sembunyikan kembali form keterangan
                $('#absence-form-container').slideUp();
            });
        });
    </script>
@endpush
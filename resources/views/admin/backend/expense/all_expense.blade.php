@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Pengeluaran</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0"> 
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#standard-modal"> Tambah Pengeluaran </button>
                </ol>
            </div>
        </div>

        <!-- Datatables  -->
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($expense as $key=> $item) 
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                                    <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>
                                        @if ($item->photo)
                                        <button class="btn btn-info btn-sm view-photo-btn" data-image-url="{{ asset($item->photo) }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#photoModal"
                                            data-bs-placement="bottom" title="Lihat Foto/Struk">
                                            Foto/Struk
                                        </button>
                                        @endif
                                        
                                        <a href="{{ route('edit.expense',$item->id) }}" class="btn btn-success btn-sm" id="edit">Edit</a>    
                                        <a href="{{ route('delete.expense',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Hapus</a>
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





 <!-- Default Modal -->
 <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="standard-modalLabel">Pengeluaran</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('store.expense') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}"> 
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" name="description" required>
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="amount" placeholder="Masukkan jumlah tanpa tanda pemisah titik" required>
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Foto (Opsional)</label>
                        <input accept=".png, .jpg, .jpeg" type="file" class="form-control" name="photo">
                    </div>

                    <div class="modal-footer"> 
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 </div>

 <div class="modal fade" tabindex="-1" role="dialog" id="photoModal" aria-labelledby="photo-modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-5" id="photo-modalLabel">Foto/Struk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                {{-- Gambar akan dimasukkan di sini oleh JavaScript --}}
                <img src="" id="modalImage" class="img-fluid">
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
                "targets": [3]
            }],
            "order": [[0, "asc"]]
        });

        // Script baru untuk handle modal gambar
        $(document).ready(function() {
            $('.view-photo-btn').on('click', function(e) {
                e.preventDefault(); // Mencegah link default berjalan

                // 1. Ambil URL gambar dari atribut data-image-url
                var imageUrl = $(this).data('image-url');

                // 2. Set atribut 'src' pada gambar di dalam modal
                $('#modalImage').attr('src', imageUrl);

                // 3. Tampilkan modal (Bootstrap akan menanganinya via data-toggle,
                //    tapi ini cara manual jika diperlukan)
                // $('#sertifikatModal').modal('show');
            });
        });
    </script>
@endpush
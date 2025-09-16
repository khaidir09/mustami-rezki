@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Produksi</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-2"> 
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#standard-modal"> Tambah Produksi </button>
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
                                <th>Nama Produksi</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($production as $key=> $item) 
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('edit.production',$item->id) }}" class="btn btn-success btn-sm" id="edit">Edit</a>    
                                        <a href="{{ route('delete.production',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Hapus</a>
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
                <h1 class="modal-title fs-5" id="standard-modalLabel">Produksi</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('store.production') }}" method="post">
                    @csrf
                    
                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}"> 
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="price" placeholder="Masukkan harga tanpa tanda pemisah titik" required>
                    </div>

                    <div class="modal-footer"> 
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
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
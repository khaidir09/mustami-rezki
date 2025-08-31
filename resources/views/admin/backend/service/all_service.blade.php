@extends('admin.admin_master')
@section('admin')

<div class="content">

    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Semua Jasa Jahit</h4>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0"> 
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#standard-modal"> Tambah Jasa </button>
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
                                <th>Komponen Jasa</th>
                                <th>Biaya Dasar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($service as $key=> $item) 
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>Rp {{ number_format($item->base_price, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($item->is_active)
                                            <span class="badge text-bg-success">Aktif</span>
                                        @else
                                            <span class="badge text-bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('edit.service',$item->id) }}" class="btn btn-success btn-sm" id="edit">Edit</a>    
                                        <a href="{{ route('delete.service',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Hapus</a>
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
                <h1 class="modal-title fs-5" id="standard-modalLabel">Komponen Jahit</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('store.service') }}" method="post">
                    @csrf
                    
                    <div class="form-group mb-3 col-md-12">
                        <label for="input1" class="form-label">Komponen</label>
                        <input type="text" class="form-control" name="name" id="input1"> 
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label for="input3" class="form-label">Biaya Dasar</label>
                        <input type="number" class="form-control" name="base_price" id="input3">
                    </div>

                    <div class="form-group mb-3 col-md-12">
                        <label for="input4" class="form-label">Status</label>
                        <select name="is_active" class="form-select" id="input4">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select></div>
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
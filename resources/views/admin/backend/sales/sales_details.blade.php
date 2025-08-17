@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-0">
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Detail Penjualan</h2>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('all.sale') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>
         

 <div class="card">
    <div class="card-body">
    <div class="row">

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 10px; transition: 0.2s">
                <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #17a2b8, #0d6efd); border-radius:10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold">Informasi Pelanggan</h5> 
                </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <strong class="me-2 text-muted">Nama:</strong>
                    <span>{{ $sales->customer->name }}</span> 
                </div>
                <div class="d-flex align-items-center mb-3">
                    <strong class="me-2 text-muted">Nomor HP/WA:</strong>
                    <span>{{ $sales->customer->phone }}</span> 
                </div> 
                <div class="d-flex align-items-center mb-3">
                    <strong class="me-2 text-muted">Alamat:</strong>
                    <span>{{ $sales->customer->address }}</span> 
                </div> 
            </div>

            </div> 
        </div>


  {{-- Purchase info --}}
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm border-0 h-100" style="border-radius: 10px; transition: 0.2s">
        <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #17a2b8, #0d6efd); border-radius:10px 10px 0 0;">
            <h5 class="mb-0 fw-bold">Informasi Penjualan</h5> 
        </div>
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3">
            <strong class="me-2 text-muted">Tanggal Penjualan:</strong>
            <span>{{ \Carbon\Carbon::parse($sales->date)->locale('id')->translatedFormat('d F Y') }}</span> 
        </div>
        <div class="d-flex align-items-center mb-3">
            <strong class="me-2 text-muted">Status:</strong>
            <span>{{ $sales->status }}</span> 
        </div>
        <div class="d-flex align-items-center mb-3">
            <strong class="me-2 text-muted">Jumlah yang dibayarkan:</strong>
            <span>Rp {{ number_format($sales->paid_amount, 0, ',', '.') }}</span> 
        </div>
        <div class="d-flex align-items-center mb-3">
            <strong class="me-2 text-muted">Jumlah terhutang:</strong>
            <span>Rp {{ number_format($sales->due_amount, 0, ',', '.') }}</span> 
        </div>
        <div class="d-flex align-items-center mb-3">
            <strong class="me-2 text-muted">Grand Total:</strong>
            <span>{{ number_format($sales->grand_total, 0)  }}</span> 
        </div> 
    </div>

    </div> 
</div>
{{-- End Purchase info --}}

 {{-- Order Summary  --}}
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 10px; transition: 0.2s">
                <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #17a2b8, #0d6efd); border-radius:10px 10px 0 0;">
                    <h5 class="mb-0 fw-bold">Ringkasan Penjualan</h5> 
                </div>


    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Diskon</th>
                    <th>Sub total</th>
                </tr>
            </thead>
        <tbody>
        @foreach ($sales->saleItems as $key => $item)  
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->net_unit_cost,0)  }}</td>
                <td>{{ number_format($item->discount,0)  }}</td>
                <td>{{ number_format($item->subtotal,0)  }}</td>
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
         </div>
      </div>
   </div>
</div>
 
@endsection
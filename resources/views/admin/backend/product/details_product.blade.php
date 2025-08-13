@extends('admin.admin_master')
@section('admin')

<div class="content"> 
    <!-- Start Content-->
    <div class="container-xxl">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0"> Detail Produk</h4>
            </div> 
            
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                     <a href="{{ route('all.product') }}" class="btn btn-dark">Kembali</a>
                </ol>
            </div>
        </div>

    <hr>
    <div class="card">
        <div class="card-body">
            <div class="row">
            {{-- // Product Image     --}}
                <div class="col-md-4">
                    <h5 class="mb-3">Foto Produk</h5>
    <div class="d-flex flex-wrap">
        @forelse ($product->images as $image)
        <img src="{{ asset($image->image) }}" alt="image" class="me-2 mb-2" width="100" height="100" style="object-fit: cover; border: 1px solid #ddd; border-radius: 5px"> 
       @empty
           <p class="text-danger">Tidak ada foto</p>
       @endforelse     

    </div> 
        </div>

        {{-- // Product Details Data     --}}
    <div class="col-md-8">
        <h5 class="mb-3">Informasi Produk</h5>
        <ul class="list-group">
            <li class="list-group-item"><strong>Kode:</strong> {{ $product->code }} </li>
            <li class="list-group-item"><strong>Nama:</strong> {{ $product->name }} </li>
            <li class="list-group-item"><strong>Kategori:</strong> {{ $product->category->category_name }} </li>
            <li class="list-group-item"><strong>Modal:</strong> Rp. {{ number_format($product->modal, 0, ',', '.') }} </li>
            <li class="list-group-item"><strong>Harga Jual:</strong> Rp. {{ number_format($product->price, 0, ',', '.') }} </li>
            <li class="list-group-item"><strong>Stok:</strong> {{ $product->product_qty }} {{ $product->satuan }}</li>
            <li class="list-group-item"><strong>Peringatan Stok Minimal:</strong> {{ $product->stock_alert }} </li>
            <li class="list-group-item"><strong>Catatan:</strong> {{ $product->note }} </li>
            <li class="list-group-item"><strong>Dibuat pada:</strong> 
             {{ \Carbon\Carbon::parse($product->created_at)->format('d F Y')  }} </li>

        </ul>

    </div>


            </div> 
        </div>

    </div>

    </div>
</div> 
 @endsection
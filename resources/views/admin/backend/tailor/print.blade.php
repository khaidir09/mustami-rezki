<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi {{ $transaction->transaction_code }}</title>
    <style>
        /* CSS untuk tampilan cetak nota */
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        .nota-container {
            width: 100%;
            border: 1px solid #eee;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .w-100 {
            width: 100%;
        }

        .w-50 {
            width: 50%;
        }

        .w-75 {
            width: 75%;
        }

        .w-25 {
            width: 25%;
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #ccc;
        }

        .header-info .info-kanan {
            text-align: right;
        }

        h5 {
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 10px;
            color: #000;
            text-decoration: underline;
        }

        p {
            margin: 0 0 5px 0;
            line-height: 1.6;
        }

        strong {
            font-weight: bold;
        }

        .header-tabel {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }

        .rincian-tabel {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .rincian-tabel th,
        .rincian-tabel td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .rincian-tabel thead {
            background-color: #f2f2f2;
        }

        .rincian-tabel .rata-tengah {
            text-align: center;
        }

        .rincian-tabel .rata-kanan {
            text-align: right;
        }

        .section {
            margin-top: 20px;
        }

        .ringkasan {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .ringkasan .catatan {
            width: 50%;
        }

        .ringkasan .total-tabel-wrapper {
            width: 50%;
        }

        .total-tabel {
            width: 100%;
            border-collapse: collapse;
        }

        .total-tabel td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .total-tabel .label {
            font-weight: normal;
        }

        .total-tabel .nilai {
            text-align: right;
            font-weight: bold;
        }

        .total-tabel .sisa-bayar {
            color: #D32F2F;
            /* Merah */
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            color: #fff;
        }

        .badge-warning {
            background-color: #FFC107;
            color: #000;
        }

        .badge-primary {
            background-color: #0D6EFD;
        }

        .badge-success {
            background-color: #198754;
        }

        .badge-dark {
            background-color: #212529;
        }

        .badge-secondary {
            background-color: #6C757D;
        }
    </style>
</head>

<body>

    <div class="nota-container">
        <table class="header-tabel">
            <tbody>
                <tr>
                    <th style="text-align: left;">Info Pelanggan</th>
                    <th style="text-align: left;">Info Transaksi</th>
                </tr>
                <tr>
                    <td><strong>Nama:</strong> {{ $transaction->customer->name ?? 'N/A' }}</td>
                    <td><strong>Kode:</strong> {{ $transaction->transaction_code }}</td>
                </tr>
                <tr>
                    <td><strong>Telepon:</strong> {{ $transaction->customer->phone ?? 'N/A' }}</td>
                    <td><strong>Tgl. Masuk:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Alamat:</strong> {{ $transaction->customer->address ?? 'N/A' }}</td>
                    <td><strong>Tgl. Diambil:</strong> {{ $transaction->updated_at ? \Carbon\Carbon::parse($transaction->updated_at)->format('d F Y') : '-' }}</td>
            </tbody>
        </table>

        <div class="section">
            <h5>Rincian Jasa:</h5>
            <table class="rincian-tabel">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jenis</th>
                        <th>Komponen</th>
                        <th class="rata-tengah">Jumlah</th>
                        <th class="rata-kanan">Harga Satuan</th>
                        <th class="rata-kanan">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaction->items as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->type->name ?? '-' }}</td>
                        <td>{{ $item->nama_komponen }}</td>
                        <td class="rata-tengah">{{ $item->quantity }}</td>
                        <td class="rata-kanan">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="rata-kanan">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="rata-tengah">Tidak ada item jasa pada transaksi ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transaction->soldProducts->isNotEmpty())
        <div class="section">
            <h5>Rincian Produk/Bahan dari Toko:</h5>
            <table class="rincian-tabel">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th class="rata-tengah">Jumlah</th>
                        <th class="rata-kanan">Harga Satuan</th>
                        <th class="rata-kanan">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->soldProducts as $index => $productItem)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $productItem->product_name ?? '-' }}</td>
                        <td class="rata-tengah">{{ $productItem->quantity }}</td>
                        <td class="rata-kanan">Rp {{ number_format($productItem->price, 0, ',', '.') }}</td>
                        <td class="rata-kanan">Rp {{ number_format($productItem->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    <div class="ringkasan">
        <table class="w-100">
            <tbody>
                <tr>
                    <th class="w-50 text-left">Catatan</th>
                    <td class="text-left">Total Biaya Jasa</td>
                    <th class="nilai text-left">: Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</th>
                </tr>
                @php
                $grandTotal = $transaction->total_price + $transaction->soldProducts->sum('subtotal');
                @endphp
                @if ($transaction->soldProducts->isNotEmpty())
                    <tr>
                        <td>{{ $transaction->description ?: 'Tidak ada catatan.' }}</td>
                        <td class="text-left">Total Harga Produk</td>
                        <th class="nilai text-left">: Rp {{ number_format($transaction->soldProducts->sum('subtotal'), 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-left">Total Tagihan</td>
                        <th class="nilai text-left">: Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-left">Jumlah Dibayar</td>
                        <th class="nilai text-left">: Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-left">Sisa Bayar</td>
                        <th class="nilai text-left">: Rp {{ number_format($transaction->due_amount, 0, ',', '.') }}</th>
                    </tr>
                @else
                    <tr>
                        <td>{{ $transaction->description ?: 'Tidak ada catatan.' }}</td>
                        <td class="text-left">Total Tagihan</td>
                        <th class="nilai text-left">: Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-left">Jumlah Dibayar</td>
                        <th class="nilai text-left">: Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-left">Sisa Bayar</td>
                        <th class="nilai text-left">: Rp {{ number_format($transaction->due_amount, 0, ',', '.') }}</th>
                    </tr>
                @endif
            </tbody>
        </table>
        <img src="{{ $imagePath }}" alt="" style="width: 200px;">
    </div>

</body>

</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi {{ $transaction->transaction_code }}</title>
    <style>
        /* -- CSS Global yang Dirapikan -- */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .nota-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* -- 1. Header Baru dengan Logo (Menggunakan Tabel) -- */
        .header-section {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0d6efd;
        }
        .header-logo {
            width: 40%;
            vertical-align: top;
        }
        .header-details {
            width: 60%;
            text-align: right;
            vertical-align: top;
        }
        .logo {
            max-width: 100px;
            height: auto;
        }
        .header-details h3 {
            margin: 0;
            font-size: 18px;
            color: #0d6efd;
        }
        .header-details p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }

        /* -- 2. Info Section (Menggunakan Tabel) -- */
        .info-section-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box {
            width: 50%;
            vertical-align: top;
        }
        .info-box h5 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 4px 0;
        }

        /* -- 3. Tabel Rincian Item (Styling Disesuaikan) -- */
        .rincian-tabel {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .rincian-tabel th,
        .rincian-tabel td {
            border-bottom: 1px solid #ddd;
            padding: 10px 8px;
            text-align: left;
        }
        .rincian-tabel th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            border-top: 1px solid #ddd;
        }
        
        /* -- 4. Ringkasan & Catatan (Menggunakan Tabel) -- */
        .summary-wrapper {
            width: 100%;
            margin-top: 10px;
        }
        /* -- Tambahan untuk Stempel Lunas -- */
        .notes-column {
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
            position: relative; /* Diperlukan agar stempel bisa diposisikan */
        }

        .stamp-container {
            text-align: center;
            margin-top: 20px;
        }

        .stamp-image {
            max-width: 140px;
            height: auto;
            opacity: 0.8;
            transform: rotate(-15deg);
        }
        .summary-column {
            width: 50%;
            vertical-align: top;
        }
        .notes-column h5 {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .summary-table tr:last-child td {
            border-bottom: none;
        }
        .summary-table .total {
            font-weight: bold;
            font-size: 14px;
            color: #0d6efd;
        }
        
        /* -- Footer -- */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="nota-container">
        <table class="header-section">
            <tr>
                <td class="header-logo">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" alt="Logo Perusahaan" class="logo">
                </td>
                <td class="header-details">
                   <h3>Mustami Rezki Tailor Shop</h3>
                    <p>Menjual Bahan Menjahit dan Menerima Jasa Jahit Pakaian</p>
                    <p>Jl. Saberan Effendi RT.03 No.064 Kel.Sungai Malang</p>
                    <p>0811-5185-665</p>
                </td>
            </tr>
        </table>

        <table class="info-section-table">
            <tr>
                <td class="info-box">
                    <h5>Info Pelanggan</h5>
                    <p><strong>Nama:</strong> {{ $transaction->customer->name ?? 'N/A' }}</p>
                    <p><strong>Telepon:</strong> {{ $transaction->customer->phone ?? 'N/A' }}</p>
                    <p><strong>Alamat:</strong> {{ $transaction->customer->address ?? 'N/A' }}</p>
                </td>
                <td class="info-box" style="text-align: right;">
                    <h5>Info Transaksi</h5>
                    <p><strong>Kode:</strong> {{ $transaction->transaction_code }}</p>
                    <p><strong>Tgl. Masuk:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') }}</p>
                    <p><strong>Tgl. Ambil:</strong> {{ $transaction->status == 'Diambil' ? \Carbon\Carbon::parse($transaction->updated_at)->format('d F Y') : '-' }}</p>
                </td>
            </tr>
        </table>
        
        <div class="section">
            <h5 style="font-weight: bold;">Rincian Jasa</h5>
            <table class="rincian-tabel">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jenis</th>
                        <th>Komponen</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaction->items as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->type->name ?? '-' }}</td>
                        <td>{{ $item->nama_komponen }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada item jasa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transaction->soldProducts->isNotEmpty())
        <div class="section">
            <h5 style="font-weight: bold;">Rincian Produk/Bahan</h5>
            <table class="rincian-tabel">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->soldProducts as $index => $productItem)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $productItem->product_name ?? '-' }}</td>
                        <td class="text-center">{{ $productItem->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($productItem->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($productItem->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <table class="summary-wrapper">
            @php
                $grandTotal = $transaction->total_price + $transaction->soldProducts->sum('subtotal');
            @endphp
            <tr>
                <td class="notes-column">
                    <h5>Catatan</h5>
                    <p>{{ $transaction->description ?: 'Tidak ada catatan.' }}</p>

                    @if($transaction->due_amount <= 0)
                        <div class="stamp-container">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="LUNAS" class="stamp-image">
                        </div>
                    @endif
                </td>

                <td class="summary-column">
                    <table class="summary-table">
                        @if ($transaction->soldProducts->isNotEmpty())
                        <tr>
                            <td>Total Biaya Jasa</td>
                            <td class="text-right">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Total Harga Produk</td>
                            <td class="text-right">Rp {{ number_format($transaction->soldProducts->sum('subtotal'), 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="total">
                            <td>Total Tagihan</td>
                            <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Jumlah Dibayar</td>
                            <td class="text-right">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                        @if($transaction->due_amount > 0)
                        <tr>
                            <td>Sisa Bayar</td>
                            <td class="text-right">Rp {{ number_format($transaction->due_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
        
        <div class="invoice-footer">
            <p>Terima kasih atas kepercayaan Anda.</p>
        </div>

    </div>
</body>
</html>
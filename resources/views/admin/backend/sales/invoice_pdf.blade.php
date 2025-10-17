<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 10mm;
        }
        h5 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        /* -- Header Baru dengan Logo (Menggunakan Tabel) -- */
        .header-section {
            width: 100%;
            border-collapse: collapse; /* Menghilangkan spasi antar sel */
            padding-bottom: 20px;
            border-bottom: 2px solid #0d6efd;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 40%; /* Alokasikan ruang untuk logo */
            vertical-align: top;
        }
        .header-details {
            width: 60%; /* Alokasikan ruang untuk detail perusahaan */
            text-align: right;
            vertical-align: top;
        }
        .logo {
            max-width: 100px; /* Atur ukuran logo Anda */
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

        /* -- Info Section yang Disesuaikan (Menggunakan Tabel) -- */
        .info-section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-box {
            width: 50%; /* Membuat setiap kolom mengambil setengah lebar */
            vertical-align: top; /* Konten sejajar di atas */
            padding: 0 5px; /* Memberi sedikit jarak antar kolom */
        }
        .info-box h5 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 4px 0;
        }
        
        /* -- Tabel yang Lebih Bersih -- */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border-bottom: 1px solid #ddd;
            padding: 10px 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            border-top: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }

        /* -- Summary Section yang Disesuaikan dengan Stempel -- */
        .summary-section-wrapper {
            width: 100%;
            margin-top: 20px;
            vertical-align: middle; /* Penting agar stempel dan tabel sejajar di tengah */
        }

        .stamp-column {
            width: 50%;
            text-align: center; /* Stempel akan berada di tengah kolom kiri */
        }

        .summary-column {
            width: 50%;
        }

        .stamp-image {
            max-width: 140px; /* Atur ukuran stempel Anda */
            height: auto;
            
            transform: rotate(-15deg); /* Opsional: memiringkan stempel */
        }

        .summary-table {
            width: 100%; /* Sekarang mengisi penuh kolom kanan */
            border-collapse: collapse;
        }
        /* Style lainnya untuk .summary-table td, .total, dll tidak perlu diubah */
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
        
        /* -- Footer Baru -- */
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
    <div class="invoice-container">
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
                    <h5>Info Penjualan</h5>
                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($sales->date)->locale('id')->translatedFormat('d F Y') }} </p>
                    <p><strong>Status:</strong> {{ $sales->status }} </p>
                </td>
            </tr>
        </table>

        <h5>Rincian Produk</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Diskon</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales->saleItems as $key => $item )
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->net_unit_cost, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->discount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-section-wrapper">
            <tr>
                <td class="stamp-column">
                    @if($sales->due_amount <= 0)
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="LUNAS" class="stamp-image">
                    @endif
                </td>

                <td class="summary-column">
                    <table class="summary-table">
                        <tr>
                            <td>Total Diskon</td>
                            <td class="text-right">Rp {{ number_format($sales->discount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ongkos Kirim</td>
                            <td class="text-right">Rp {{ number_format($sales->shipping, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Jumlah Dibayar</td>
                            <td class="text-right">Rp {{ number_format($sales->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                        @if($sales->due_amount > 0)
                        <tr>
                            <td>Sisa Bayar</td>
                            <td class="text-right">Rp {{ number_format($sales->due_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="total">
                            <td>Grand Total</td>
                            <td class="text-right">Rp {{ number_format($sales->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="invoice-footer">
            <p>Terima kasih telah berbelanja di Mustami Rezki Tailor Shop.</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
        </div>

    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Bulanan - {{ \Carbon\Carbon::create($summary->year, $summary->month)->translatedFormat('F Y') }}</title>
    <style>
        /* CSS untuk tampilan cetak */
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #333; }
        .container { width: 95%; margin: 0 auto; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .summary-table { font-size: 14px; }
        .no-print { display: none; }
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Laporan Arus Kas Bulanan</h1>
        <p><strong>Mustami Rezki</strong></p>
        <p>Periode: {{ \Carbon\Carbon::create($summary->year, $summary->month)->translatedFormat('F Y') }}</p>
        <button onclick="window.print()" class="no-print" style="padding: 10px 20px; cursor: pointer;">Cetak Laporan</button>
    </div>

    {{-- Ringkasan Utama --}}
    <h3>Ringkasan Keuangan</h3>
    <table class="summary-table">
        <tr>
            <th width="50%">Saldo Awal Bulan</th>
            <th class="text-right">@rupiah($summary->opening_balance)</th>
        </tr>
        <tr>
            <td>(+) Total Pemasukan</td>
            <td class="text-right" style="color: green;">@rupiah($summary->total_income)</td>
        </tr>
        <tr>
            <td>(-) Total Pengeluaran</td>
            <td class="text-right" style="color: red;">(@rupiah($summary->total_expense))</td>
        </tr>
        <tr>
            <th style="background-color: #f2f2f2;">Saldo Akhir Bulan</th>
            <th class="text-right" style="background-color: #f2f2f2;">@rupiah($summary->closing_balance)</th>
        </tr>
    </table>

    {{-- Tabel Total Pemasukan per Kategori --}}
    <h3>Rincian Total Pemasukan</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Kategori Transaksi</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Penjualan Produk</td>
                <td class="text-right">@rupiah($totalSales)</td>
            </tr>
            <tr>
                <td>Jasa Jahit</td>
                <td class="text-right">@rupiah($totalTailor)</td>
            </tr>
            <tr>
                <td>Produksi</td>
                <td class="text-right">@rupiah($totalProduction)</td>
            </tr>
            <tr>
                <td>Penerimaan Lainnya</td>
                <td class="text-right">@rupiah($totalAcceptance)</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f9f9f9;">Total Pemasukan</td>
                <td class="text-right" style="font-weight: bold; background-color: #f9f9f9;">@rupiah($totalSales + $totalTailor + $totalProduction + $totalAcceptance)</td>
            </tr>
        </tbody>
    </table>

    {{-- Tabel Total Pengeluaran per Kategori --}}
    <h3>Rincian Total Pengeluaran</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Kategori Pengeluaran</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pembelian Barang</td>
                <td class="text-right">@rupiah($totalPurchase)</td>
            </tr>
            <tr>
                <td>Biaya Operasional</td>
                <td class="text-right">@rupiah($totalOperational)</td>
            </tr>
            <tr>
                <td>Gaji & Komisi</td>
                <td class="text-right">@rupiah($totalPayroll)</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f9f9f9;">Total Pengeluaran</td>
                <td class="text-right" style="font-weight: bold; background-color: #f9f9f9;">@rupiah($totalPurchase + $totalOperational + $totalPayroll)</td>
            </tr>
        </tbody>
    </table>
    
    {{-- Break page --}}
    <div style="page-break-after: always;"></div>

    {{-- Rincian Pemasukan --}}
    <h3>Detail Transaksi Pemasukan</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Sumber</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->date }}</td>
                <td>Penjualan Produk</td>
                <td>{{ $sale->note ?? '-' }}</td>
                <td class="text-right">@rupiah($sale->grand_total)</td>
            </tr>
            @endforeach
            @foreach($tailorTransactions as $tailor)
            <tr>
                <td>{{ $tailor->transaction_date }}</td>
                <td>Jasa Jahit</td>
                <td>Nota #{{ $tailor->transaction_code }}</td>
                <td class="text-right">@rupiah($tailor->total_price + $tailor->soldProducts->sum('subtotal'))</td>
            </tr>
            @endforeach
            @foreach($productions as $production)
            <tr>
                <td>{{ $production->date }}</td>
                <td>Produksi</td>
                <td>{{ $production->name }}</td>
                <td class="text-right">@rupiah($production->total_price)</td>
            </tr>
            @endforeach
            @foreach($acceptances as $acceptance)
            <tr>
                <td>{{ $acceptance->date }}</td>
                <td>Penerimaan</td>
                <td>{{ $acceptance->description }}</td>
                <td class="text-right">@rupiah($acceptance->amount)</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Rincian Pengeluaran --}}
    <h3>Detail Transaksi Pengeluaran</h3>
     <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
            <tr>
                <td>{{ $purchase->date }}</td>
                <td>Pembelian Barang</td>
                <td>Nota #{{ $purchase->purchase_no }}</td>
                <td class="text-right">@rupiah($purchase->total_amount)</td>
            </tr>
            @endforeach
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->date }}</td>
                <td>{{ $expense->category }}</td>
                <td>{{ $expense->description }}</td>
                <td class="text-right">@rupiah($expense->amount)</td>
            </tr>
            @endforeach
            @foreach($payrolls as $payroll)
            <tr>
                <td>{{ $payroll->payment_date }}</td>
                <td>Gaji & Komisi</td>
                <td>{{ $payroll->description }} - {{ $payroll->user->name ?? '' }}</td>
                <td class="text-right">@rupiah($payroll->amount)</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem pada tanggal {{ now()->format('d F Y H:i') }}</p>
    </div>
</div>

</body>
</html>
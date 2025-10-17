<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Transaksi {{ $transaction->transaction_code }}</title>
    <style>
        @page {
            size: A4;
            margin: 3mm;
            /* Atur margin atas, kanan, bawah, dan kiri */
        }

        body {
            margin: 0;
        }

        .pt-5 {
            padding-top: 20px;
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

        .text-justify {
            text-align: justify;
        }

        .capital {
            text-transform: uppercase;
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

        td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            padding: 4px;
        }

        #data {
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <table class="w-100">
        <tr>
            <td class="text-center" style="width: 30%">
                <img src="{{ $imagePath }}" height="70">
            </td>
        </tr>
    </table>

    <hr style="border-top: 1px dashed;">

    <h4 class="text-center" style="margin-bottom: 6px; margin-top: 6px;">NOTA PENGAMBILAN JAHIT</h4>

    <table class="w-100">
        <thead>
            <tr style="border-top-style: solid; border-right-style: solid;">
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Info Pelanggan
                </th>
                <th id="data" colspan="4" class="text-left" style="border-left-style: solid;">Info Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Nama</th>
                <td id="data" class="capital">: {{ $items->customer->nama }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Jenis Barang</th>
                <td id="data" class="capital">: {{ $items->type->name }}</td>
                <td id="data" scope="row">IMEI/SN</th>
                <td id="data">: {{ $items->imei }}</td>
            </tr>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Nomor HP</th>
                <td id="data">: {{ $items->customer->nomor_hp }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Merek</th>
                <td id="data" class="capital">: {{ $items->brand->name }}</td>
                <td id="data" scope="row">Kelengkapan</th>
                    @if ($items->kelengkapan != null)
                <td id="data" class="capital">: {{ $items->kelengkapan }}</td>
            @else
                <td id="data" class="capital">: Hanya Unit</td>
                @endif
            </tr>
            <tr style="border-bottom-style: solid; border-right-style: solid;">
                <td scope="row" style="border-left-style: solid;">Alamat</th>
                <td class="capital">: {{ $items->customer->alamat }}</td>
                <td scope="row" style="border-left-style: solid;">Model Seri</th>
                <td class="capital">: {{ $items->modelserie->name }}</td>
                <td scope="row">Warna/Kapasitas</th>
                <td class="capital">: {{ $items->warna }} / {{ $items->capacity->name }}</td>
            </tr>
        </tbody>
    </table>
    <table class="w-100" style="padding-top: 0px;">
        <thead>
            <tr style="border-top-style: solid; border-right-style: solid;">
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Tindakan</th>
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Pengecekan
                    (Tombol, Kamera, dll)</th>
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Kerusakan</th>
                <td id="data" class="capital">: {{ $items->kerusakan }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Fungsi (Masuk)</th>
                <td id="data" class="capital">: {{ $items->qc_masuk }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Total Biaya Servis</td>
                <td id="data">: Rp. {{ number_format($items->biaya) }}</td>
            </tr>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Kondisi Servis</th>
                <td id="data" class="capital">: {{ $items->kondisi_servis }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Fungsi (Keluar)</th>
                <td id="data" class="capital">: {{ $items->qc_keluar }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Metode Pembayaran</td>
                <td id="data" class="capital">: {{ $items->cara_pembayaran }}</td>
            </tr>
            @if ($items->uang_muka != null && $items->diskon != null)
                <tr style="border-right-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;">Tindakan Servis</td>
                    <td id="data" class="capital">
                        @if (json_decode($items->tindakan_servis))
                            @foreach (json_decode($items->tindakan_servis) as $key => $tindakan)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">{{ $tindakan }}
                                        (<strong>{{ Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') ? 'Garansi ' . Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') : 'Garansi tidak ada' }}</strong>)
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            : {{ $items->tindakan_servis }}
                        @endif
                    </td>
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;">Diskon</td>
                    <td id="data">: Rp. {{ number_format($items->diskon) }}</td>
                </tr>
                <tr style="border-right-style: solid;">
                    @if (json_decode($items->biaya_j))
                        <td id="data" scope="row" style="border-left-style: solid;">Rincian Biaya Servis</td>
                        <td id="data">
                            @foreach (json_decode($items->biaya_j) as $key => $biaya)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">
                                        {{ json_decode($items->tindakan_servis)[$key] . ' = Rp. ' . number_format($biaya) }}
                                    </li>
                                </ul>
                            @endforeach
                        </td>
                    @endif
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;">Uang Muka</td>
                    <td id="data">: Rp.
                        {{ number_format($items->uang_muka) }}</td>
                </tr>
                <tr style="border-bottom-style: solid; border-right-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;">Sisa Pembayaran</td>
                    <td id="data">: Rp. {{ number_format($items->biaya - $items->uang_muka - $items->diskon) }}
                    </td>
                </tr>
            @elseif ($items->uang_muka != null && $items->diskon === null)
                <tr style="border-right-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;">Tindakan Servis</td>
                    <td id="data" class="capital">
                        @if (json_decode($items->tindakan_servis))
                            @foreach (json_decode($items->tindakan_servis) as $key => $tindakan)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">{{ $tindakan }}
                                        (<strong>{{ Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') ? 'Garansi ' . Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') : 'Garansi tidak ada' }}</strong>)
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            : {{ $items->tindakan_servis }}
                        @endif
                    </td>
                    @if (json_decode($items->biaya_j))
                        <td id="data" scope="row" style="border-left-style: solid;">Rincian Biaya Servis</td>
                        <td id="data">
                            @foreach (json_decode($items->biaya_j) as $key => $biaya)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">
                                        {{ json_decode($items->tindakan_servis)[$key] . ' = Rp. ' . number_format($biaya) }}
                                    </li>
                                </ul>
                            @endforeach
                        </td>
                    @endif
                    <td id="data" scope="row" style="border-left-style: solid;">Uang Muka</td>
                    <td id="data">: Rp. {{ number_format($items->uang_muka) }}</td>
                </tr>
                <tr style="border-bottom-style: solid; border-right-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;">Sisa Pembayaran</td>
                    <td id="data">: Rp. {{ number_format($items->biaya - $items->uang_muka) }}</td>
                </tr>
            @elseif ($items->diskon != null && $items->uang_muka === null)
                <tr style="border-right-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;">Tindakan Servis</td>
                    <td id="data" class="capital">
                        @if (json_decode($items->tindakan_servis))
                            @foreach (json_decode($items->tindakan_servis) as $key => $tindakan)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">{{ $tindakan }}
                                        (<strong>{{ Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') ? 'Garansi ' . Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') : 'Garansi tidak ada' }}</strong>)
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            : {{ $items->tindakan_servis }}
                        @endif
                    </td>
                    @if (json_decode($items->biaya_j))
                        <td id="data" scope="row" style="border-left-style: solid;">Rincian Biaya Servis</td>
                        <td id="data">
                            @foreach (json_decode($items->biaya_j) as $key => $biaya)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">
                                        {{ json_decode($items->tindakan_servis)[$key] . ' = Rp. ' . number_format($biaya) }}
                                    </li>
                                </ul>
                            @endforeach
                        </td>
                    @endif
                    <td id="data" scope="row" style="border-left-style: solid;">Diskon</td>
                    <td id="data">: Rp. {{ number_format($items->diskon) }}</td>
                </tr>
                <tr style="border-bottom-style: solid; border-right-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;"></td>
                    <td id="data"></td>
                    <td id="data" scope="row" style="border-left-style: solid;">Sisa Pembayaran</td>
                    <td id="data">: Rp. {{ number_format($items->biaya - $items->diskon) }}</td>
                </tr>
            @elseif ($items->diskon === null && $items->uang_muka === null)
                <tr style="border-right-style: solid; border-bottom-style: solid;">
                    <td id="data" scope="row" style="border-left-style: solid;">Tindakan Servis</td>
                    <td id="data" class="capital">
                        @if (json_decode($items->tindakan_servis))
                            @foreach (json_decode($items->tindakan_servis) as $key => $tindakan)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">{{ $tindakan }}
                                        (<strong>{{ Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') ? 'Garansi ' . Carbon\Carbon::make(json_decode($items->exp_garansi_j)[$key])->format('Y-m-d') : 'Garansi tidak ada' }}</strong>)
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            : {{ $items->tindakan_servis }}
                        @endif
                    </td>
                    @if (json_decode($items->biaya_j))
                        <td id="data" scope="row" style="border-left-style: solid;">Rincian Biaya Servis</td>
                        <td id="data" colspan="3">
                            @foreach (json_decode($items->biaya_j) as $key => $biaya)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">
                                        {{ json_decode($items->tindakan_servis)[$key] . ' = Rp. ' . number_format($biaya) }}
                                    </li>
                                </ul>
                            @endforeach
                        </td>
                    @endif
                    @if ($items->cara_pembayaran === 'Tunai & Transfer')
                        <td id="data" scope="row" style="border-left-style: solid;">Jumlah Pembayaran</td>
                        <td id="data">: Tunai Rp. {{ number_format($items->tunai) }} & Transfer Rp.
                            {{ number_format($items->transfer) }} </td>
                    @elseif ($items->cara_pembayaran === 'Kredit')
                        <td id="data" scope="row" style="border-left-style: solid;">Tempo</td>
                        <td id="data">: Rp. {{ number_format($items->due) }}
                            ({{ \Carbon\Carbon::parse($items->tempo)->locale('id')->translatedFormat('d F Y') }})</td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table>
    <table class="w-100">
        <tbody>
            <tr>
                @if ($items->catatan != null)
                    <td colspan="4">
                        <strong>Catatan</strong> : {{ $items->catatan }}
                    </td>
                @endif
            </tr>
            <tr>
                <th class="text-left w-75">Syarat & Ketentuan</th>
                @if ($items->exp_garansi === null)
                    <th colspan="3" class="w-25 text-right">
                        {{-- (Tidak ada garansi) --}}
                    </th>
                @else
                    <th colspan="3" class="w-25 text-right">
                        {{-- (Garansi <strong>{{ $items->exp_garansi }}</strong>) --}}
                    </th>
                @endif
            </tr>
            <tr>
                <td rowspan="2" class="text-justify" style="font-style: italic; padding-right: 30px;">
                    {!! $terms->description !!}
                </td>
                <th class="text-center" style="vertical-align: top;">Pengambil</th>
                <th class="text-center" style="vertical-align: top;">Penyerah</th>
                <th class="text-center" style="vertical-align: top;">Teknisi</th>
            </tr>
            <tr>
                <td class="pt-5 text-center capital">{{ $items->pengambil }}</td>
                @if ($items->penyerah != null)
                    <td class="pt-5 text-center capital">{{ $items->penyerah }}</td>
                @else
                    <td class="pt-5 text-center capital">-</td>
                @endif
                @if ($items->user != null)
                    <td class="pt-5 text-center capital">{{ $items->user->name }}</td>
                @else
                    <td class="pt-5 text-center capital">-</td>
                @endif
            </tr>
        </tbody>
    </table>
</body>

</html>
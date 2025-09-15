<?php

namespace App\Helpers;

use App\Models\TailorTransaction;

class WhatsAppHelper
{
    public static function generateTailorInvoiceLink(TailorTransaction $transaction)
    {
        // 1. Ambil nomor telepon pelanggan, format ke 62
        $phone = $transaction->customer->phone;
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        $phone = preg_replace('/[^0-9]/', '', $phone); // Hapus karakter selain angka

        // 2. Format rincian item
        $itemDetails = "";
        foreach ($transaction->items as $item) {
            // Format: "- 2 Kemeja Lengan Panjang"
            $itemDetails .= "- " . $item->quantity . " " . $item->nama_komponen . "\n";
        }
        // Hapus baris baru terakhir
        $itemDetails = rtrim($itemDetails, "\n");


        // 3. Bangun template pesan
        $template = "Assalamualaikum Wr. Wb.\n"
            . "*Yth. Bapak/Ibu " . $transaction->customer->name . "*\n\n"
            . "Ijin kami informasikan untuk jahitan dengan:\n\n"
            . "No. Nota: " . $transaction->transaction_code . "\n"
            . "Tanggal Masuk: _" . \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') . "_\n\n"
            . "*Rincian Pesanan:*\n"
            . $itemDetails . "\n\n"
            . "*Status:*\n"
            . "#$transaction->status\n\n"
            . "*Total Biaya:* " . 'Rp ' . number_format($transaction->total_price, 0, ',', '.') . "\n"
            . "*Telah Dibayar:* " . 'Rp ' . number_format($transaction->paid_amount, 0, ',', '.') . "\n"
            . "*Sisa Bayar:* *" . 'Rp ' . number_format($transaction->due_amount, 0, ',', '.') . "*\n\n"
            . "Terima kasih atas kepercayaan Anda.\n\n"
            . "Hormat kami,\n"
            . "_Mustami Rezki Tailorshop_";

        // 4. Encode pesan untuk URL
        $encodedMessage = urlencode($template);

        // 5. Kembalikan link lengkap
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}

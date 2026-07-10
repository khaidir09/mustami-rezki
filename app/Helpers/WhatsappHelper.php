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

        $serviceDetails = "";
        if ($transaction->items->isNotEmpty()) {
            foreach ($transaction->items as $item) {
                $serviceDetails .= "- " . $item->quantity . " " . $item->nama_komponen . "\n";
            }
        }

        // 3. BUAT RINCIAN BARU UNTUK PRODUK/BAHAN
        $productDetails = "";
        if ($transaction->soldProducts->isNotEmpty()) {
            // Tambahkan judul baru jika ada produk
            $productDetails .= "\n*Produk/Bahan dari Toko:*\n";
            foreach ($transaction->soldProducts as $productItem) {
                // Format: - 1 Meter Kain Katun (@ Rp 50.000)
                $productDetails .= "- " . $productItem->quantity . " " . $productItem->product->satuan . " " . $productItem->product_name
                    . " (@ " . 'Rp ' . number_format($productItem->price, 0, ',', '.') . ")\n";
            }
        }

        // 4. Hitung ulang total tagihan gabungan
        $grandTotal = $transaction->items->sum('subtotal') + $transaction->soldProducts->sum('subtotal');
        // Sisa bayar juga dihitung ulang dari total gabungan
        $dueAmount = $grandTotal - $transaction->paid_amount;

        // Hapus baris baru terakhir
        $serviceDetails = rtrim($serviceDetails, "\n");


        // 3. Bangun template pesan
        $template = "=================\n"
            . "Assalamualaikum Wr. Wb.\n"
            . "*Yth. Bapak/Ibu " . $transaction->customer->name . "*\n\n"
            . "Ijin kami informasikan untuk jahitan dengan:\n\n"
            . "No. Nota: " . $transaction->transaction_code . "\n"
            . "Tanggal Masuk: _" . \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y') . "_\n\n"
            . "*Rincian Pesanan:*\n"
            . $serviceDetails  . "\n"
            . $productDetails . "\n"
            . "*Status:*\n"
            . "#$transaction->status\n\n"
            . "*Total Biaya:* " . 'Rp ' . number_format($grandTotal, 0, ',', '.') . "\n"
            . "*Telah Dibayar:* " . 'Rp ' . number_format($transaction->paid_amount, 0, ',', '.') . "\n"
            . "*Sisa Bayar:* *" . 'Rp ' . number_format($dueAmount, 0, ',', '.') . "*\n\n"
            . "Terima kasih atas kepercayaan Anda.\n\n"
            . "Hormat kami,\n"
            . "_Mustami Rezki Tailorshop_\n"
            . "=================";

        // 4. Encode pesan untuk URL
        $encodedMessage = urlencode($template);

        // 5. Kembalikan link lengkap
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}

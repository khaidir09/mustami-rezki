<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profit_distributions', function (Blueprint $table) {
            $table->id();
            // Kolom untuk Polymorphic Relationship
            // Ini akan menyimpan ID dan tipe model dari transaksi asal (Sale, JasaJahit, dll.)
            $table->unsignedBigInteger('transaction_id');
            $table->string('transaction_type');

            // Jenis alokasi profit
            $table->enum('distribution_type', ['pengembangan_modal', 'pribadi', 'sedekah']);

            // Jumlah nominal yang dialokasikan
            $table->decimal('amount', 15, 0);

            $table->timestamps();

            // Menambahkan index untuk performa query yang lebih cepat
            $table->index(['transaction_id', 'transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_distributions');
    }
};

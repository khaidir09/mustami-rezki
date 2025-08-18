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
        Schema::create('tailor_transaction_items', function (Blueprint $table) {
            $table->id();
            // Foreign key ke tabel transaksi jahit utama
            $table->foreignId('tailor_transaction_id')
                ->constrained('tailor_transactions')
                ->onDelete('cascade');

            // Foreign key ke tabel master jasa
            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');

            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 0);
            $table->decimal('subtotal', 15, 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tailor_transaction_items');
    }
};

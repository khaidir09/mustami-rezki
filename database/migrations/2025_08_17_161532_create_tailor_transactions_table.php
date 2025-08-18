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
        Schema::create('tailor_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();

            // Foreign key ke tabel customers
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();

            // Kolom untuk finansial
            $table->decimal('cost_price', 15, 0)->default(0);
            $table->decimal('total_price', 15, 0)->default(0);
            $table->decimal('profit', 15, 0)->default(0);
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->decimal('due_amount', 15, 0)->default(0);

            // Kolom untuk status pengerjaan
            $table->enum('status', ['Antrian', 'Dikerjakan', 'Selesai', 'Diambil'])->default('Antrian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tailor_transactions');
    }
};

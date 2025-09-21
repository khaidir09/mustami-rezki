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
        Schema::create('financial_summaries', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('opening_balance', 15, 0)->comment('Saldo Awal Bulan');
            $table->decimal('total_income', 15, 0)->comment('Total Pemasukan');
            $table->decimal('total_expense', 15, 0)->comment('Total Pengeluaran');
            $table->decimal('closing_balance', 15, 0)->comment('Saldo Akhir Bulan');
            $table->string('status')->default('closed'); // Status laporan: 'closed', 'active'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_summaries');
    }
};

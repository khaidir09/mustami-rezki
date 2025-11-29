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
        Schema::create('daily_financial_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('opening_balance', 15, 0)->comment('Saldo Awal Hari');
            $table->decimal('total_income', 15, 0)->comment('Total Pemasukan Hari Ini');
            $table->decimal('total_expense', 15, 0)->comment('Total Pengeluaran Hari Ini');
            $table->decimal('closing_balance', 15, 0)->comment('Saldo Akhir Hari');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_financial_summaries');
    }
};

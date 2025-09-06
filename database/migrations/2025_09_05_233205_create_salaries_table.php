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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // Jenis pembayaran: 'daily_salary', 'weekly_commission'
            $table->integer('amount'); // Jumlah yang dibayarkan
            $table->date('payment_date'); // Tanggal pembayaran dilakukan
            $table->date('period_start')->nullable(); // Awal periode (untuk komisi mingguan)
            $table->date('period_end')->nullable(); // Akhir periode (untuk komisi mingguan)
            $table->text('description')->nullable(); // Catatan, misal: "Gaji Harian" atau "Komisi Minggu ke-3 September"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};

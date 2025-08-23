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
        Schema::table('tailor_transactions', function (Blueprint $table) {
            // Menentukan tipe pengerjaan
            $table->enum('work_type', ['Internal', 'Eksternal'])->default('Internal')->after('id');

            // Kolom untuk penjahit eksternal (supplier jasa)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null')->after('tailor_id');

            // Buat kolom tailor_id menjadi nullable, karena jika eksternal, tailor_id akan kosong
            $table->unsignedBigInteger('tailor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tailor_transactions', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['work_type', 'supplier_id']);
            $table->unsignedBigInteger('tailor_id')->nullable(false)->change();
        });
    }
};

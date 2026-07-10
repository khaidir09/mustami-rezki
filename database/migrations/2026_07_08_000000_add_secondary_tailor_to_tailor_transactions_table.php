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
            $table->unsignedBigInteger('secondary_tailor_id')->nullable()->after('tailor_id');
            $table->foreign('secondary_tailor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Bobot pembagian komisi antar penjahit. Penjahit tunggal: primary = 100, secondary = null.
            $table->decimal('primary_tailor_pct', 5, 2)->nullable()->after('secondary_tailor_id');
            $table->decimal('secondary_tailor_pct', 5, 2)->nullable()->after('primary_tailor_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tailor_transactions', function (Blueprint $table) {
            $table->dropForeign(['secondary_tailor_id']);
            $table->dropColumn(['secondary_tailor_id', 'primary_tailor_pct', 'secondary_tailor_pct']);
        });
    }
};

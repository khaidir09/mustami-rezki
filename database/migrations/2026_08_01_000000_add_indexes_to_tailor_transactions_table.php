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
            $table->index('status', 'tailor_transactions_status_index');
            $table->index('transaction_date', 'tailor_transactions_transaction_date_index');

            // Dipakai laporan omzet & distribusi profit yang selalu memfilter
            // status = 'Diambil' lalu me-range picked_up_at.
            $table->index(['status', 'picked_up_at'], 'tailor_transactions_status_picked_up_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tailor_transactions', function (Blueprint $table) {
            $table->dropIndex('tailor_transactions_status_index');
            $table->dropIndex('tailor_transactions_transaction_date_index');
            $table->dropIndex('tailor_transactions_status_picked_up_at_index');
        });
    }
};

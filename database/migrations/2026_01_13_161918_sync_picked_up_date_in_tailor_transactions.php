<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tailor_transactions')
            ->join('profit_distributions', 'tailor_transactions.id', '=', 'profit_distributions.transaction_id')
            ->where('profit_distributions.transaction_type', 'App\Models\TailorTransaction') // Filter tipe model
            ->update([
                'tailor_transactions.picked_up_at' => DB::raw('profit_distributions.created_at')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tailor_transactions', function (Blueprint $table) {
            //
        });
    }
};

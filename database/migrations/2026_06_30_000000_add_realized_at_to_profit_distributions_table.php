<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profit_distributions', function (Blueprint $table) {
            // Tanggal realisasi profit (basis pelaporan), terpisah dari created_at (audit insert).
            // Tailor: picked_up_at; Sale/Production: tanggal transaksi.
            $table->dateTime('realized_at')->nullable()->after('amount');
            $table->index('realized_at');
        });

        // Backfill baris lama dari tanggal sumber masing-masing tipe.
        // Dilakukan per-baris (chunk) agar portabel lintas MySQL & sqlite.
        DB::table('profit_distributions')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $realizedAt = null;

                switch ($row->transaction_type) {
                    case 'App\Models\TailorTransaction':
                    case 'App\Models\TailorTransactionProduct':
                        // transaction_id untuk kedua tipe ini menunjuk ke tailor_transactions.id
                        $realizedAt = DB::table('tailor_transactions')
                            ->where('id', $row->transaction_id)
                            ->value('picked_up_at');
                        break;

                    case 'App\Models\Sale':
                        $realizedAt = DB::table('sales')
                            ->where('id', $row->transaction_id)
                            ->value('date');
                        break;

                    case 'App\Models\Production':
                        $realizedAt = DB::table('productions')
                            ->where('id', $row->transaction_id)
                            ->value('date');
                        break;
                }

                // Fallback bila sumber tidak ditemukan / tanggal kosong.
                $realizedAt = $realizedAt ?: $row->created_at;

                DB::table('profit_distributions')
                    ->where('id', $row->id)
                    ->update(['realized_at' => $realizedAt]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_distributions', function (Blueprint $table) {
            $table->dropIndex(['realized_at']);
            $table->dropColumn('realized_at');
        });
    }
};

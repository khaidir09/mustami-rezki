<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Consolidate existing data: Sum amounts for each transaction
        // We select the first ID for each group to keep, and update its amount.
        // Then we delete the rest.

        // This logic is complex in SQL directly, so we'll use a cursor or temporary logic if possible.
        // Alternatively, since we are moving to 1 row per transaction, we can:
        // 1. Create a temporary table with aggregated data.
        // 2. Truncate the original table.
        // 3. Insert aggregated data back.

        // However, to be safe with IDs and foreign keys (if any, though none defined in schema),
        // a safer approach is to iterate or use a subquery update.

        // Strategy:
        // 1. Fetch all unique (transaction_id, transaction_type) pairs.
        // 2. For each pair, sum the amount.
        // 3. Update one record with the sum.
        // 4. Delete the other records for that pair.

        // Since we can't easily iterate in migration without models (and models might change),
        // we'll try a SQL-based approach.

        // Step 1: Create a temporary table with the sums
        DB::statement("CREATE TEMPORARY TABLE profit_sums AS
            SELECT transaction_id, transaction_type, SUM(amount) as total_amount, MIN(id) as keep_id
            FROM profit_distributions
            GROUP BY transaction_id, transaction_type");

        // Step 2: Delete rows that are NOT the ones we want to keep
        DB::statement("DELETE FROM profit_distributions
            WHERE id NOT IN (SELECT keep_id FROM profit_sums)");

        // Step 3: Update the kept rows with the summed amount
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE profit_distributions
                SET amount = (SELECT total_amount FROM profit_sums WHERE keep_id = profit_distributions.id)
                WHERE id IN (SELECT keep_id FROM profit_sums)");
        } else {
            // MySQL update with join
            DB::statement("UPDATE profit_distributions pd
                JOIN profit_sums ps ON pd.id = ps.keep_id
                SET pd.amount = ps.total_amount");
        }

        // Step 4: Drop the column
        if (Schema::hasColumn('profit_distributions', 'distribution_type')) {
            Schema::table('profit_distributions', function (Blueprint $table) {
                $table->dropColumn('distribution_type');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("DROP TABLE profit_sums");
        } else {
            DB::statement("DROP TEMPORARY TABLE profit_sums");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_distributions', function (Blueprint $table) {
            $table->enum('distribution_type', ['pengembangan_modal', 'pribadi', 'sedekah'])->nullable();
        });

        // Note: We cannot easily restore the split amounts exactly as they were (1/3 split)
        // unless we assume they were perfectly split.
        // For 'down', we will just leave them as one row with null distribution_type
        // OR we could try to split them back into 3 rows.
        // For simplicity and safety, we just restore the column.
    }
};

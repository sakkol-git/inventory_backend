<?php

// database/migrations/2024_01_01_000004_add_balance_after_to_transactions.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('balance_after')
                ->nullable() // nullable for backfill safety
                ->after('quantity');
            $table->text('notes')->nullable()->after('balance_after');
        });
        // Backfill balance_after from current stock table where possible
        // (best-effort; historical records will remain NULL)
        if (!app()->runningUnitTests() && DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE transactions t
                JOIN plant_stocks ps
                    ON ps.id = t.transactionable_id
            
                JOIN (
                    SELECT *
                    FROM (
                        SELECT
                            MAX(id) AS max_id,
                            transactionable_id,
                            transactionable_type
                        FROM transactions
                        GROUP BY transactionable_id, transactionable_type
                    ) latest
                ) latest_tx
                    ON latest_tx.max_id = t.id
            
                SET t.balance_after = ps.quantity
            
                WHERE t.transactionable_type =
                'App\\\\Modules\\\\Inventory\\\\Models\\\\PlantStock'
            ");
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['balance_after', 'notes']);
        });
    }
};

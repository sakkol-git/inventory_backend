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
        //
        DB::statement(
            'UPDATE transactions AS t
            SET t.user_id = NULL
            WHERE t.user_id IS NOT NULL
            AND NOT EXISTS (
                SELECT 1 FROM users u WHERE u.id = t.user_id
            )'
        );
        Schema::table('transactions', function (Blueprint $table) {
            // FK: user_id → users(id) NULL on delete (preserve audit trail)

            // Composite index for all polymorphic lookups on this table
            $table->index(
                ['transactionable_type', 'transactionable_id', 'created_at'],
                'txn_morphable_created_idx'
            );
            // Index for per-user transaction history queries
            $table->index('user_id', 'txn_user_idx');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('txn_user_idx');
            $table->dropIndex('txn_morphable_created_idx');
            $table->dropForeign(['user_id']);
        });
    }
};

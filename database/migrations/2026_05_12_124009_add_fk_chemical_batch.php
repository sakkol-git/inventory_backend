<?php

// database/migrations/2024_01_01_000002_add_fk_chemical_batches.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean orphans first
        DB::statement(
            'DELETE FROM chemical_batches
            WHERE chemical_id NOT IN (SELECT id FROM chemicals)'
        );
        Schema::table('chemical_batches', function (Blueprint $table) {

            // Add status column
            $table->enum('status', ['active', 'near_expiry', 'expired', 'disposed'])
                ->default('active')
                ->after('expiry_date');

            // ADD THIS COLUMN FIRST
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->after('status');

            // Index
            $table->index(['expiry_date', 'status'], 'cb_expiry_status_idx');

            // Foreign key
            $table->foreign('responsible_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
        // Backfill status for already-expired batches
        DB::statement(
            "UPDATE chemical_batches
            SET status = 'expired'
            WHERE expiry_date < NOW() AND status = 'active'"
        );
        DB::statement(
            "UPDATE chemical_batches
            SET status = 'near_expiry'
            WHERE expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
             AND status = 'active'"
        );
    }

    public function down(): void
    {
        Schema::table('chemical_batches', function (Blueprint $table) {
            $table->dropIndex('cb_expiry_status_idx');
            $table->dropForeign(['responsible_user_id']);
            $table->dropForeign(['chemical_id']);
            $table->dropColumn('status');
        });
    }
};

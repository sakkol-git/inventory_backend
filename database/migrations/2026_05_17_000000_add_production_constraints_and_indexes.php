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
        $driver = DB::getDriverName();

        // Add CHECK constraint to prevent negative plant stock quantity (MySQL/MariaDB only)
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE plant_stocks ADD CONSTRAINT plant_stocks_quantity_check CHECK (quantity >= 0)');
            DB::statement('ALTER TABLE plant_stocks ADD CONSTRAINT plant_stocks_reserved_check CHECK (reserved_quantity >= 0 AND reserved_quantity <= quantity)');
            DB::statement('ALTER TABLE chemicals ADD CONSTRAINT chemicals_quantity_check CHECK (quantity >= 0)');
        }

        // Add UNIQUE constraint on equipment code
        Schema::table('equipment', function (Blueprint $table) {
            $table->unique('equipment_code');
        });

        // Add UNIQUE constraint on chemical code
        Schema::table('chemicals', function (Blueprint $table) {
            $table->unique('chemical_code');
        });

        // Add indexes for query performance
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['created_at', 'status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['user_id', 'action']);
        });

        Schema::table('chemical_usage_logs', function (Blueprint $table) {
            $table->index('chemical_id');
            $table->index('user_id');
            $table->index('used_at');
        });

        Schema::table('plant_stocks', function (Blueprint $table) {
            $table->index('plant_species_id');
            $table->index('plant_variety_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        // Remove CHECK constraints (MySQL/MariaDB only)
        if (in_array($driver, ['mysql', 'mariadb'])) {
            try {
                DB::statement('ALTER TABLE plant_stocks DROP CONSTRAINT plant_stocks_quantity_check');
                DB::statement('ALTER TABLE plant_stocks DROP CONSTRAINT plant_stocks_reserved_check');
                DB::statement('ALTER TABLE chemicals DROP CONSTRAINT chemicals_quantity_check');
            } catch (Exception) {
                // Constraints may not exist
            }
        }

        // Remove UNIQUE constraints
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropUnique(['equipment_code']);
        });

        Schema::table('chemicals', function (Blueprint $table) {
            $table->dropUnique(['chemical_code']);
        });

        // Remove indexes
        Schema::table('borrow_records', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['action']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'action']);
        });

        Schema::table('chemical_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['chemical_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['used_at']);
        });

        Schema::table('plant_stocks', function (Blueprint $table) {
            $table->dropIndex(['plant_species_id']);
            $table->dropIndex(['plant_variety_id']);
            $table->dropIndex(['status']);
        });
    }
};

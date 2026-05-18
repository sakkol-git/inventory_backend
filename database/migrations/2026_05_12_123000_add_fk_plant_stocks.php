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
        DB::statement('UPDATE plant_stocks SET quantity = 0 WHERE quantity < 0');
        Schema::table('plant_stocks', function (Blueprint $table) {

            // Fk: plant stock -> plant samples (cascade on delete: remove stock if sample gone)

            // Fk: plant stock -> plant varieties (restrict: cannot delete variety if stock exists)

            // Composite index for stock lookup
            $table->index(
                ['plant_sample_id', 'plant_variety_id'],
                'ps_sample_variety_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_stocks', function (Blueprint $table) {
            $table->dropIndex('ps_sample_variety_idx');
        });
    }
};

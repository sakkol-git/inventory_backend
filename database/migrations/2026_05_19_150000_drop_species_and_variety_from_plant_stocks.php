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
        if (! Schema::hasTable('plant_stocks')) {
            return;
        }

        $speciesExists = Schema::hasColumn('plant_stocks', 'plant_species_id');
        $varietyExists = Schema::hasColumn('plant_stocks', 'plant_variety_id');

        if (($speciesExists || $varietyExists) && !app()->runningUnitTests() && DB::getDriverName() === 'mysql') {
            $constraints = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN (?, ?) AND REFERENCED_TABLE_NAME IS NOT NULL',
                ['plant_stocks', 'plant_species_id', 'plant_variety_id']
            );

            foreach ($constraints as $constraint) {
                try {
                    DB::statement("ALTER TABLE plant_stocks DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // Ignore if the foreign key was already removed or does not exist
                }
            }
        }
        
        if ($speciesExists || $varietyExists) {
            if (Schema::hasIndex('plant_stocks', 'plant_stocks_sample_idx', 'index')) {
                Schema::table('plant_stocks', function (Blueprint $table) {
                    $table->dropIndex('plant_stocks_sample_idx');
                });
            } elseif (Schema::hasIndex('plant_stocks', 'plant_stocks_sample_idx')) {
                 Schema::table('plant_stocks', function (Blueprint $table) {
                    $table->dropIndex('plant_stocks_sample_idx');
                });
            }

            Schema::table('plant_stocks', function (Blueprint $table) use ($speciesExists, $varietyExists): void {
                if (DB::getDriverName() !== 'sqlite') {
                    if ($speciesExists) {
                        $table->dropColumn('plant_species_id');
                    }

                    if ($varietyExists) {
                        $table->dropColumn('plant_variety_id');
                    }
                }
            });
        }

        if (!Schema::hasIndex('plant_stocks', 'plant_stocks_sample_idx')) {
            Schema::table('plant_stocks', function (Blueprint $table): void {
                $table->index(['plant_sample_id', 'status'], 'plant_stocks_sample_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('plant_stocks', 'plant_stocks_sample_idx')) {
            Schema::table('plant_stocks', function (Blueprint $table): void {
                $table->dropIndex('plant_stocks_sample_idx');
            });
        }

        Schema::table('plant_stocks', function (Blueprint $table): void {
            $table->foreignId('plant_species_id')
                ->constrained('plant_species')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('plant_variety_id')
                ->nullable()
                ->constrained('plant_varieties')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index(['plant_species_id', 'plant_variety_id', 'plant_sample_id', 'status'], 'plant_stocks_sample_idx');
        });
    }
};

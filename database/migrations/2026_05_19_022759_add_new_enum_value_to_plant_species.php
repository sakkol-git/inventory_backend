<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!app()->runningUnitTests() && DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE plant_species
                MODIFY growth_type ENUM(
                    'herb',
                    'shrub',
                    'tree',
                    'vine',
                    'grass',
                    'aquatic',
                    'other'
                ) NULL
            ");
        }
    }

    public function down(): void
    {
        if (!app()->runningUnitTests() && DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE plant_species
                MODIFY growth_type ENUM(
                    'annual',
                    'perennial',
                    'biennial'
                ) NULL
            ");
        }
    }
};

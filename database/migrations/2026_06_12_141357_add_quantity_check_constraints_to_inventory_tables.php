<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE chemicals ADD CONSTRAINT chk_chemicals_qty_non_negative CHECK (quantity >= 0)');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE plant_stocks ADD CONSTRAINT chk_plant_stocks_qty_non_negative CHECK (quantity >= 0)');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE plant_stocks ADD CONSTRAINT chk_plant_stocks_reserved_qty_non_negative CHECK (reserved_quantity >= 0)');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE plant_stocks ADD CONSTRAINT chk_plant_stocks_available_qty CHECK (quantity >= reserved_quantity)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE chemicals DROP CONSTRAINT chk_chemicals_qty_non_negative');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE plant_stocks DROP CONSTRAINT chk_plant_stocks_qty_non_negative');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE plant_stocks DROP CONSTRAINT chk_plant_stocks_reserved_qty_non_negative');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE plant_stocks DROP CONSTRAINT chk_plant_stocks_available_qty');
    }
};

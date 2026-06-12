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
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'App\Modules\Inventory\Models\Equipment')->update(['borrowable_type' => 'equipment']);
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'App\Modules\Inventory\Models\Chemical')->update(['borrowable_type' => 'chemical']);
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'App\Modules\Inventory\Models\PlantStock')->update(['borrowable_type' => 'plant_stock']);
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'App\Modules\Inventory\Models\PlantSample')->update(['borrowable_type' => 'plant_sample']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'equipment')->update(['borrowable_type' => 'App\Modules\Inventory\Models\Equipment']);
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'chemical')->update(['borrowable_type' => 'App\Modules\Inventory\Models\Chemical']);
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'plant_stock')->update(['borrowable_type' => 'App\Modules\Inventory\Models\PlantStock']);
        \Illuminate\Support\Facades\DB::table('borrow_records')->where('borrowable_type', 'plant_sample')->update(['borrowable_type' => 'App\Modules\Inventory\Models\PlantSample']);
    }
};

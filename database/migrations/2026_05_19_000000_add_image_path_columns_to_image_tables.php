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
        Schema::table('chemicals', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
        });

        Schema::table('plant_varieties', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
        });

        Schema::table('plant_samples', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
        });

        Schema::table('plant_families', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_families', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_url']);
        });

        Schema::table('plant_samples', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('plant_varieties', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('chemicals', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};

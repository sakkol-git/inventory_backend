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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['item_type', 'item_id'], 'transactions_item_type_id_index');
        });

        Schema::table('borrow_records', function (Blueprint $table) {
            $table->index(['borrowable_type', 'borrowable_id'], 'borrow_records_borrowable_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_item_type_id_index');
        });

        Schema::table('borrow_records', function (Blueprint $table) {
            $table->dropIndex('borrow_records_borrowable_type_id_index');
        });
    }
};

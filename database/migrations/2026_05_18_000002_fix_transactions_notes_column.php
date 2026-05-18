<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        $hasNote = Schema::hasColumn('transactions', 'note');
        $hasNotes = Schema::hasColumn('transactions', 'notes');

        if ($hasNotes && ! $hasNote) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->string('note')->nullable()->after('quantity');
            });

            DB::statement('UPDATE transactions SET note = notes WHERE note IS NULL');

            Schema::table('transactions', function (Blueprint $table): void {
                $table->dropColumn('notes');
            });

            return;
        }

        if ($hasNotes && $hasNote) {
            DB::statement('UPDATE transactions SET note = COALESCE(note, notes)');

            Schema::table('transactions', function (Blueprint $table): void {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        if (! Schema::hasColumn('transactions', 'notes')) {
            Schema::table('transactions', function (Blueprint $table): void {
                $table->text('notes')->nullable()->after('note');
            });
        }
    }
};

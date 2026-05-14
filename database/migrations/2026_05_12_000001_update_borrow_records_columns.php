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
        if (! Schema::hasColumn('borrow_records', 'reviewed_at')) {
            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            });
        }

        if (! Schema::hasColumn('borrow_records', 'rejected_reason')) {
            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->text('rejected_reason')->nullable()->after('reviewed_at');
            });
        }

        if (Schema::hasColumn('borrow_records', 'review_at')) {
            DB::table('borrow_records')
                ->whereNotNull('review_at')
                ->update(['reviewed_at' => DB::raw('review_at')]);

            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->dropColumn('review_at');
            });
        }

        if (Schema::hasColumn('borrow_records', 'reject_reason')) {
            DB::table('borrow_records')
                ->whereNotNull('reject_reason')
                ->update(['rejected_reason' => DB::raw('reject_reason')]);

            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->dropColumn('reject_reason');
            });
        }

        $this->makeBorrowedAtNullable();
    }

    public function down(): void
    {
        if (! Schema::hasColumn('borrow_records', 'review_at')) {
            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->timestamp('review_at')->nullable()->after('reviewed_by');
            });
        }

        if (! Schema::hasColumn('borrow_records', 'reject_reason')) {
            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->text('reject_reason')->nullable()->after('review_at');
            });
        }

        if (Schema::hasColumn('borrow_records', 'reviewed_at')) {
            DB::table('borrow_records')
                ->whereNotNull('reviewed_at')
                ->update(['review_at' => DB::raw('reviewed_at')]);

            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->dropColumn('reviewed_at');
            });
        }

        if (Schema::hasColumn('borrow_records', 'rejected_reason')) {
            DB::table('borrow_records')
                ->whereNotNull('rejected_reason')
                ->update(['reject_reason' => DB::raw('rejected_reason')]);

            Schema::table('borrow_records', function (Blueprint $table): void {
                $table->dropColumn('rejected_reason');
            });
        }

        DB::table('borrow_records')
            ->whereNull('borrowed_at')
            ->update(['borrowed_at' => now()]);

        $this->makeBorrowedAtNotNullable();
    }

    private function makeBorrowedAtNullable(): void
    {
        if (! Schema::hasColumn('borrow_records', 'borrowed_at')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE borrow_records ALTER COLUMN borrowed_at DROP NOT NULL');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE borrow_records MODIFY borrowed_at DATETIME NULL');

            return;
        }

        Schema::table('borrow_records', function (Blueprint $table): void {
            $table->dateTime('borrowed_at')->nullable()->change();
        });
    }

    private function makeBorrowedAtNotNullable(): void
    {
        if (! Schema::hasColumn('borrow_records', 'borrowed_at')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE borrow_records ALTER COLUMN borrowed_at SET NOT NULL');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE borrow_records MODIFY borrowed_at DATETIME NOT NULL');

            return;
        }

        Schema::table('borrow_records', function (Blueprint $table): void {
            $table->dateTime('borrowed_at')->nullable(false)->change();
        });
    }
};

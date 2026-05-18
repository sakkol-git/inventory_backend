<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Generates sequential human-readable codes for entities.
 *
 * Pattern: PREFIX-001, PREFIX-002, ...
 *
 * Uses pessimistic locking to prevent duplicate codes under concurrency.
 */
class CodeGeneratorService
{
    /**
     * Generate the next code for a given model.
     *
     * Wraps the read+increment in a transaction with a row-level lock
     * to prevent race conditions from generating duplicate codes.
     *
     * @param  class-string<Model>  $modelClass  The Eloquent model class
     * @param  string  $prefix  Code prefix (e.g. "EXP", "CON")
     * @param  string  $column  The column that stores the code
     */
    public static function next(string $modelClass, string $prefix, string $column): string
    {
        return DB::transaction(function () use ($modelClass, $prefix, $column): string {
            $query = $modelClass::query();

            // Include soft-deleted records to avoid code collisions
            if (in_array(SoftDeletes::class, class_uses_recursive($modelClass))) {
                $query->withTrashed();
            }

            // Lock the rows to prevent concurrent reads from getting the same max
            $lastCode = $query->lockForUpdate()->max($column);

            if ($lastCode) {
                $numericPart = (int) substr($lastCode, strlen($prefix) + 1);
                $nextNum = $numericPart + 1;
            } else {
                $nextNum = 1;
            }

            return sprintf('%s-%03d', $prefix, $nextNum);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Core\Concerns;

use App\Modules\Core\Services\ImageUpload\ImageUploadService;
use Illuminate\Database\Eloquent\Model;

/**
 * HasImageUpload — mixin for Eloquent models that store images.
 *
 * Provides:
 *  • `image` accessor that resolves the single public URL
 *  • Auto-delete of uploaded files when the model is soft-deleted
 *
 * Models using this trait MUST have `image_url` and `image_path` columns.
 *
 * @mixin Model
 * @method static void deleting(callable $callback)
 * @method string getTable()
 */
trait HasImageUpload
{
    /**
     * Boot: register model event listeners.
     */
    public static function bootHasImageUpload(): void
    {
        // Clean up uploaded files when a record is deleted
        static::deleting(function (Model $model): void {
            resolve(ImageUploadService::class)->deleteImageForModel($model);
        });
    }

    /**
     * Single resolved image URL (uploaded file takes priority).
     */
    protected function getImageAttribute(): ?string
    {
        return ImageUploadService::resolveImageUrl(
            $this->getAttribute('image_path'),
            $this->getAttribute('image_url'),
        );
    }

    /**
     * The storage sub-folder name for this entity.
     * Override in each model if a custom folder is desired.
     */
    public static function imageFolder(): string
    {
        return strtolower(str_replace('_', '-', (new static())->getTable()));
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Core\Services\ImageUpload;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorageService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = env('IMAGE_STORAGE_DISK', config('filesystems.default'));
    }

    public function storeFile(UploadedFile $file, string $folder): string
    {
        $name = Str::ulid().'.'.$file->getClientOriginalExtension();
        $path = "images/{$folder}/{$name}";

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        $result = $disk->putFileAs("images/{$folder}", $file, $name, ['visibility' => 'public']);

        if ($result === false || ! $disk->exists($path)) {
            throw new \RuntimeException("Image upload failed for disk {$this->disk} path {$path}");
        }

        return $path;
    }

    public function deleteModelImagePath(?Model $existing): void
    {
        if (! $existing instanceof Model) {
            return;
        }

        $this->deletePath($existing->getAttribute('image_path'));
    }

    public function deletePath(?string $path): void
    {
        if (! $path) {
            return;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    public function resolveUploadedPathUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($path);
    }
}

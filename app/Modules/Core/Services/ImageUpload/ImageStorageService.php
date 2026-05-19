<?php

declare(strict_types=1);

namespace App\Modules\Core\Services\ImageUpload;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
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
        if ($this->disk === 's3') {
            $this->assertS3CredentialsConfigured();
        }

        $name = Str::ulid().'.'.$file->getClientOriginalExtension();
        $path = "images/{$folder}/{$name}";

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        $result = $disk->putFileAs("images/{$folder}", $file, $name, ['visibility' => 'public']);

        if ($result === false || ! $disk->exists($path)) {
            throw new \RuntimeException("Image upload failed for disk {$this->disk} path {$path}");
        }

        return $path;
    }

    private function assertS3CredentialsConfigured(): void
    {
        $key = config('filesystems.disks.s3.key');
        $secret = config('filesystems.disks.s3.secret');

        if (! is_string($key) || $key === '' || ! is_string($secret) || $secret === '') {
            throw new \RuntimeException(
                'S3 storage is configured for image uploads, but AWS_ACCESS_KEY_ID and/or AWS_SECRET_ACCESS_KEY are not set.'
            );
        }
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

        /** @var FilesystemAdapter $disk */
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

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($path);
    }
}

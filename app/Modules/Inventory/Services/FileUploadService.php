<?php

// app/Modules/Inventory/Services/FileUploadService.php

namespace App\Modules\Inventory\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    // Allowed MIME types per context — checked via fileinfo, NOT extension
    private const ALLOWED_MIMES = [
        'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
    ];

    // Forbidden MIME types — rejected regardless of context
    private const FORBIDDEN_MIMES = [
        'application/x-php',
        'application/x-sh',
        'application/x-executable',
        'text/x-php',
        'application/x-httpd-php',
    ];

    private const MAX_SIZES = [
        'image' => 5 * 1024 * 1024, // 5 MB
        'document' => 10 * 1024 * 1024, // 10 MB
    ];

    /**
     * Validate and store a file, returning the storage path.
     *
     * @param  string  $context  "image" or "document"
     * @param  string  $folder  Storage subfolder (e.g. "plant-images")
     * @param  string  $disk  Laravel disk name (default "private")
     * @return string Storage path (relative to disk root)
     *
     * @throws ValidationException If MIME type or size is invalid
     */
    public function validateAndStore(
        UploadedFile $file,
        string $context,
        string $folder,
        string $disk = 'private'
    ): string {
        // 1. Detect real MIME type from file content (not extension or client header)
        $realMime = mime_content_type($file->getRealPath()) ?: $file->getMimeType() ?: 'application/octet-stream';
        // 2. Hard reject forbidden MIME types
        if (in_array($realMime, self::FORBIDDEN_MIMES, true)) {
            throw ValidationException::withMessages([
                'file' => ["File type '$realMime' is not permitted."],
            ]);
        }
        // 3. Validate against allowed MIME types for this context
        $allowed = self::ALLOWED_MIMES[$context] ?? [];
        if (! in_array($realMime, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => [
                    "Invalid file type '$realMime'. Allowed: ".implode(', ', $allowed),
                ],
            ]);
        }
        // 4. Validate file size
        $maxSize = self::MAX_SIZES[$context] ?? (5 * 1024 * 1024);
        if ($file->getSize() > $maxSize) {
            $maxMb = $maxSize / 1024 / 1024;
            throw ValidationException::withMessages([
                'file' => ["File exceeds maximum size of {$maxMb}MB."],
            ]);
        }
        // 5. Generate a random filename (never trust original filename)
        $extension = $this->extensionFromMime($realMime);
        $filename = Str::uuid().'.'.$extension;

        // 6. Store in private disk
        $path = $file->storeAs($folder, $filename, $disk);

        if ($path === false) {
            throw new \App\Exceptions\StorageException("Failed to store document in {$folder}.");
        }

        return $path;
    }

    /**
     * Generate a time-limited signed URL for downloading a private file.
     * Works with local disks by creating a signed route instead of driver-dependent temporaryUrl().
     *
     * @param  int  $documentId  Document/UserDocument ID (used in signed route)
     * @param  int  $minutes  URL expiry in minutes (default 60)
     */
    public function signedDownloadUrl(int $documentId, int $minutes = 60): string
    {
        return URL::signedRoute(
            'user-documents.download',
            ['userDocument' => $documentId],
            now()->addMinutes($minutes)
        );
    }

    private function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            default => 'bin',
        };
    }
}

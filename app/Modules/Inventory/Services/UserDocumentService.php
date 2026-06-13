<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\UserDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDocumentUploadJob;
use Throwable;

class UserDocumentService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    /**
     * Store a new user document and persist the uploaded file.
     */
    public function create(UploadedFile $file, array $data, int $userId): UserDocument
    {
        // 1. Store temporarily on private disk (shared across containers)
        $tempPath = $this->fileUploadService->validateAndStore(
            file: $file,
            context: 'document',
            folder: 'temp_documents',
            disk: 'private'
        );

        try {
            return DB::transaction(function () use ($userId, $data, $file, $tempPath) {
                // 2. Create the document record with 'processing' status
                // file_path initially holds final intended path, though file isn't there yet
                // ProcessDocumentUploadJob will place it there.
                $finalFilename = basename($tempPath);
                $finalPath = 'documents/' . $finalFilename;

                $document = UserDocument::create([
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'file_path' => $finalPath,
                    'file_type' => $data['file_type'] ?? 'document',
                    'file_size' => $file->getSize(),
                    'description' => $data['description'] ?? null,
                    'status' => 'processing',
                ]);

                // 3. Dispatch job synchronously to move file to final storage.
                // This ensures the document doesn't get stuck in 'processing' if a queue worker isn't running.
                ProcessDocumentUploadJob::dispatchSync($document->id, $tempPath, $finalPath);

                return $document;
            });
        } catch (Throwable $throwable) {
            // Clean up temporary file if DB transaction fails
            Storage::disk('private')->delete($tempPath);

            throw $throwable;
        }
    }

    /**
     * Delete a user document and its physical file.
     */
    public function delete(UserDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            $document->delete();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\UserDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $path = $this->fileUploadService->validateAndStore(
            file: $file,
            context: 'document',
            folder: 'documents',
        );

        try {
            return DB::transaction(fn () => UserDocument::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'file_path' => $path,
                'file_type' => $data['file_type'] ?? 'document',
                'file_size' => $file->getSize(),
                'description' => $data['description'] ?? null,
            ]));
        } catch (Throwable $throwable) {
            Storage::disk('private')->delete($path);

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

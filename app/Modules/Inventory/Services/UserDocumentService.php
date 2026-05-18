<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\UserDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserDocumentService
{
    /**
     * Store a new user document and persist the uploaded file.
     */
    public function create(UploadedFile $file, array $data, int $userId): UserDocument
    {
        $path = $file->store('documents', 'private');

        return DB::transaction(fn () => UserDocument::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'file_path' => $path,
            'file_type' => $data['file_type'] ?? 'other',
            'file_size' => $file->getSize(),
            'description' => $data['description'] ?? null,
        ]));
    }

    /**
     * Delete a user document and its physical file.
     */
    public function delete(UserDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            if (Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }

            $document->delete();
        });
    }
}

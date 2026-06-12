<?php

namespace App\Jobs;

use App\Modules\Inventory\Models\UserDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessDocumentUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $documentId,
        public string $tempPath,
        public string $finalPath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $document = UserDocument::find($this->documentId);
        if (!$document) {
            Storage::disk('local')->delete($this->tempPath);
            return;
        }

        try {
            // Read from local temp storage and write to private storage (which could be S3)
            $fileContents = Storage::disk('local')->get($this->tempPath);
            Storage::disk('private')->put($this->finalPath, $fileContents);

            // Update document status
            $document->update(['status' => 'active', 'file_path' => $this->finalPath]);

            // Clean up temp file
            Storage::disk('local')->delete($this->tempPath);
        } catch (Throwable $e) {
            $document->update(['status' => 'failed']);
            throw $e;
        }
    }
}

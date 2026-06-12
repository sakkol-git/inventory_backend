<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentUploadJob;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Services\FileUploadService;
use App\Modules\Inventory\Services\UserDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserDocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_creation_dispatches_job_and_avoids_orphans()
    {
        Queue::fake();
        Storage::fake('local');
        Storage::fake('private');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('test.pdf', '%PDF-1.4 fake pdf content');

        $fileUploadService = new FileUploadService();
        $service = new UserDocumentService($fileUploadService);

        $data = [
            'title' => 'Test Document',
            'file_type' => 'document',
            'description' => 'Test Description',
        ];

        $document = $service->create($file, $data, $user->id);

        $this->assertEquals('processing', $document->status);
        $this->assertDatabaseHas('user_documents', [
            'id' => $document->id,
            'status' => 'processing',
        ]);

        Queue::assertPushed(ProcessDocumentUploadJob::class);
    }

    public function test_db_failure_cleans_up_local_temp_file()
    {
        Storage::fake('local');
        Storage::fake('private');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('test2.pdf', '%PDF-1.4 fake pdf content');

        $fileUploadService = new FileUploadService();
        $service = new UserDocumentService($fileUploadService);

        $data = [
            'title' => null, // title is required, this will cause DB exception
            'file_type' => 'document',
        ];

        try {
            $service->create($file, $data, $user->id);
            $this->fail('Expected exception was not thrown');
        } catch (\Throwable $e) {
            // Verify DB is empty
            $this->assertDatabaseCount('user_documents', 0);
            
            // Verify local disk is empty
            $this->assertEmpty(Storage::disk('local')->allFiles());
            
            // Verify private disk is empty
            $this->assertEmpty(Storage::disk('private')->allFiles());
        }
    }
}

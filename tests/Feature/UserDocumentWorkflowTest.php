<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\UserDocument;
use App\Modules\Inventory\Resources\UserDocumentResource;
use App\Modules\Inventory\Services\UserDocumentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserDocumentWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('student');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type', 20);
            $table->unsignedInteger('file_size');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_it_stores_a_user_document_on_private_storage(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        $service = app(UserDocumentService::class);
        $file = UploadedFile::fake()->createWithContent('policy.pdf', "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        $document = $service->create($file, [
            'title' => 'Lab Policy',
            'file_type' => 'document',
            'description' => 'Uploaded for production use',
        ], $user->id);

        $this->assertDatabaseHas('user_documents', [
            'id' => $document->id,
            'user_id' => $user->id,
            'title' => 'Lab Policy',
            'file_type' => 'document',
        ]);

        $this->assertTrue(Storage::disk('private')->exists($document->file_path));
    }

    public function test_it_deletes_the_private_file_when_the_document_is_deleted(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();
        Storage::disk('private')->put('documents/test.pdf', 'file contents');

        $document = UserDocument::create([
            'user_id' => $user->id,
            'title' => 'Archived Report',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'document',
            'file_size' => 13,
            'description' => null,
        ]);

        app(UserDocumentService::class)->delete($document);

        $this->assertFalse(Storage::disk('private')->exists('documents/test.pdf'));
        $this->assertSoftDeleted('user_documents', [
            'id' => $document->id,
        ]);
    }

    public function test_user_document_resource_hides_file_path_and_exposes_download_url(): void
    {
        $user = User::factory()->make([
            'name' => 'Test User',
        ]);
        $user->id = 99;

        $document = new UserDocument([
            'user_id' => 99,
            'title' => 'Safety Sheet',
            'file_path' => 'documents/safety.pdf',
            'file_type' => 'document',
            'file_size' => 2048,
            'description' => 'Safety document',
        ]);
        $document->id = 15;
        $document->setRelation('user', $user);

        $resource = new UserDocumentResource($document);
        $data = $resource->toArray(new HttpRequest());

        $this->assertArrayNotHasKey('file_path', $data);
        $this->assertArrayHasKey('download_url', $data);
        $this->assertSame('Safety Sheet', $data['title']);
        $this->assertSame(99, $data['user']['id']);
        $this->assertSame('Test User', $data['user']['name']);
    }
}

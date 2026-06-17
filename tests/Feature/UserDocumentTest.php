<?php

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\UserDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    public function test_user_can_upload_document()
    {
        Permission::create(['name' => 'documents.create', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->givePermissionTo('documents.create');

        $file = UploadedFile::fake()->createWithContent('test.pdf', '%PDF-1.4 this is a fake pdf content');

        $response = $this->actingAs($user, 'api')->post('/api/v1/user-documents', [
            'title' => 'My Test Document',
            'file_type' => 'pdf',
            'file' => $file,
        ]);
        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'My Test Document');

        $this->assertDatabaseHas('user_documents', [
            'title' => 'My Test Document',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_document_title()
    {
        Permission::create(['name' => 'documents.edit', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->givePermissionTo('documents.edit');

        $document = UserDocument::create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'file_path' => 'documents/old.pdf',
            'file_type' => 'pdf',
            'file_size' => 100,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user, 'api')->putJson("/api/v1/user-documents/{$document->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.title', 'New Title');

        $this->assertDatabaseHas('user_documents', [
            'id' => $document->id,
            'title' => 'New Title',
        ]);
    }
}

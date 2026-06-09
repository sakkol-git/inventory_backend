<?php

namespace Tests\Feature\Modules\Inventory;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\BorrowStatus;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BorrowLifecycleFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $admin;
    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\UserSeeder::class);
        
        $this->student = User::factory()->create(['role' => 'student']);
        $this->student->assignRole('student');
        
        $this->admin = User::factory()->admin()->create();
        $this->admin->assignRole('admin');
        
        $this->equipment = Equipment::factory()->create();
    }

    public function test_student_can_request_borrow(): void
    {
        $response = $this->actingAs($this->student, 'api')
            ->postJson('/api/v1/borrow-records', [
                'borrowable_type' => 'equipment',
                'borrowable_id' => $this->equipment->id,
                'quantity' => 1,
                'notes' => 'For chemistry lab',
                'due_at' => now()->addDays(7)->toIso8601String(),
            ]);

        $response->dump()->assertStatus(201);
        $this->assertDatabaseHas('borrow_records', [
            'user_id' => $this->student->id,
            'borrowable_id' => $this->equipment->id,
            'status' => BorrowStatus::PENDING->value,
        ]);
    }

    public function test_admin_can_approve_borrow_request(): void
    {
        $record = BorrowRecord::factory()->create([
            'user_id' => $this->student->id,
            'borrowable_id' => $this->equipment->id,
            'status' => BorrowStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/borrow-records/{$record->id}/approve");

        $response->assertStatus(200);
        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'status' => BorrowStatus::BORROWED->value,
            'reviewed_by' => $this->admin->id,
        ]);
    }

    public function test_student_cannot_approve_own_borrow_request(): void
    {
        $record = BorrowRecord::factory()->create([
            'user_id' => $this->student->id,
            'status' => BorrowStatus::PENDING,
        ]);

        $response = $this->actingAs($this->student, 'api')
            ->postJson("/api/v1/borrow-records/{$record->id}/approve");

        $response->assertStatus(403);
    }

    public function test_admin_can_reject_borrow_request(): void
    {
        $record = BorrowRecord::factory()->create([
            'status' => BorrowStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/borrow-records/{$record->id}/reject", [
                'rejected_reason' => 'Equipment is under maintenance',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'status' => BorrowStatus::REJECTED->value,
            'rejected_reason' => 'Equipment is under maintenance',
        ]);
    }

    public function test_admin_can_mark_borrowed_item_as_returned(): void
    {
        $record = BorrowRecord::factory()->borrowed()->create();

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/borrow-records/{$record->id}/return");

        $response->assertStatus(200);
        $this->assertDatabaseHas('borrow_records', [
            'id' => $record->id,
            'status' => BorrowStatus::RETURNED->value,
        ]);
        $this->assertNotNull($record->refresh()->returned_at);
    }
}

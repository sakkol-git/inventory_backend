<?php

namespace Tests\Feature\Modules\Inventory;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Enums\EquipmentCategory;
use App\Modules\Inventory\Enums\EquipmentStatus;
use App\Modules\Inventory\Enums\EquipmentCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BorrowNotificationFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure roles and permissions exist
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $labManagerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'lab_manager', 'guard_name' => 'api']);
        $studentRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student', 'guard_name' => 'api']);
        $createBorrowPermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'borrows.create', 'guard_name' => 'api']);
        $approveBorrowPermission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'borrows.approve', 'guard_name' => 'api']);
        $studentRole->givePermissionTo($createBorrowPermission);

        $this->user = User::factory()->create();
        $this->user->assignRole($studentRole);
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        
        $this->equipment = Equipment::factory()->create([
            'category' => EquipmentCategory::MICROSCOPE,
            'status' => EquipmentStatus::AVAILABLE,
            'condition' => EquipmentCondition::GOOD,
        ]);
    }

    public function test_borrow_succeeds_even_if_notification_fails()
    {
        // Mock notification to throw an exception
        Notification::fake();
        
        // In Laravel, Notification::fake() replaces the dispatcher. 
        // We can't easily make it throw with just fake().
        // Instead, we can bind a mock to the Notification dispatcher.
        
        $this->mock(\Illuminate\Contracts\Notifications\Dispatcher::class, function ($mock) {
            $mock->shouldReceive('send')->andThrow(new \Exception('Simulated notification failure'));
            $mock->shouldReceive('sendNow')->andThrow(new \Exception('Simulated notification failure'));
        });

        // We also want to assert that a warning was logged.
        Log::shouldReceive('warning')
            ->once()
            ->with('Failed to dispatch borrow request notification', \Mockery::type('array'));
            
        Log::shouldReceive('error')->zeroOrMoreTimes();
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('withContext')->zeroOrMoreTimes();

        $payload = [
            'borrowable_type' => 'equipment',
            'borrowable_id' => $this->equipment->id,
            'quantity' => 1,
            'due_at' => now()->addDays(7)->toDateTimeString(),
            'notes' => 'Need this for my project',
        ];

        $this->withoutExceptionHandling();

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/borrow-records', $payload);

        // It should still return 201 Created
        $response->assertStatus(201);
        
        // And the record should exist in the database
        $this->assertDatabaseHas('borrow_records', [
            'borrowable_id' => $this->equipment->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }
}

<?php

namespace Tests\Unit\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Policies\BorrowRecordPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BorrowRecordPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BorrowRecordPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new BorrowRecordPolicy();
        
        // Ensure permissions and roles exist
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_view_any_allows_user_with_borrows_view_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('borrows.view');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_any_denies_user_without_permission_and_without_role(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_view_any_allows_lab_manager_role(): void
    {
        $user = User::factory()->create(['role' => 'lab_manager']);
        $user->assignRole('lab_manager');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_allows_owner_of_record(): void
    {
        $user = User::factory()->create();
        $record = BorrowRecord::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $record));
    }

    public function test_view_allows_user_with_permission_not_owner(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('borrows.view');
        $record = BorrowRecord::factory()->create();

        $this->assertTrue($this->policy->view($user, $record));
    }

    public function test_create_allows_user_with_create_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('borrows.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_approve_allows_user_with_approve_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('borrows.approve');
        $record = BorrowRecord::factory()->create();

        $this->assertTrue($this->policy->approve($user, $record));
    }

    public function test_approve_denies_user_without_permission(): void
    {
        $user = User::factory()->create();
        $record = BorrowRecord::factory()->create();

        $this->assertFalse($this->policy->approve($user, $record));
    }
}

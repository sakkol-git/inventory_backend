<?php

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_user_can_view_transactions()
    {
        Permission::findOrCreate('transactions.view', 'api');
        $user = User::factory()->create();

        // Ensure user does not have 'transactions.view' permission
        $response = $this->actingAs($user, 'api')->getJson('/api/v1/transactions');

        // Since we modified TransactionController to allow standard users to hit the endpoint
        // but only see their own transactions, it should return 200 OK.
        $response->assertStatus(200);
    }

    public function test_admin_user_can_view_all_transactions()
    {
        $user = User::factory()->create();
        Permission::findOrCreate('transactions.view', 'api');
        $user->givePermissionTo('transactions.view');

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/transactions');

        $response->assertStatus(200);
    }
}

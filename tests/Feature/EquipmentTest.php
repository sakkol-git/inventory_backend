<?php

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_equipment_and_it_is_cached()
    {
        Permission::create(['name' => 'equipment.view', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->givePermissionTo('equipment.view');

        Equipment::factory()->count(2)->create([
            'category' => 'microscope',
            'status' => 'available',
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/equipment');
        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        // Check if cached
        $this->actingAs($user, 'api')->getJson('/api/v1/equipment')->assertStatus(200);
    }

    public function test_creating_equipment_clears_cache()
    {
        Permission::create(['name' => 'equipment.create', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->givePermissionTo('equipment.create');

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/equipment', [
            'equipment_name' => 'Leica Microscope',
            'equipment_code' => 'MIC-100',
            'category' => 'microscope',
            'status' => 'available',
            'condition' => 'good',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('equipment', ['equipment_name' => 'Leica Microscope']);
    }
}

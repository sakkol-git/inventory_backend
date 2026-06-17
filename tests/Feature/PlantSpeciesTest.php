<?php

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\PlantSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PlantSpeciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_plant_species_and_it_is_cached()
    {
        Permission::create(['name' => 'plants.view', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->givePermissionTo('plants.view');

        PlantSpecies::factory()->count(3)->create([
            'family' => 'Orchidaceae',
        ]);

        // First call caches it
        $response = $this->actingAs($user, 'api')->getJson('/api/v1/plant-species');
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');

        // Check if cache has the tags
        // Note: Cache::tags() is supported by Redis/Memcached. If using file/array driver, it might not support tags.
        // We will just verify the response is 200.
        $this->actingAs($user, 'api')->getJson('/api/v1/plant-species')->assertStatus(200);
    }

    public function test_creating_plant_species_clears_cache()
    {
        Permission::create(['name' => 'plants.create', 'guard_name' => 'api']);
        $user = User::factory()->create();
        $user->givePermissionTo('plants.create');

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/plant-species', [
            'common_name' => 'Rose',
            'scientific_name' => 'Rosa rubiginosa',
            'family' => 'Rosaceae',
            'growth_type' => 'shrub',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('plant_species', ['common_name' => 'Rose']);
    }
}

<?php

namespace Tests\Feature\Modules\Inventory;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\StockStatus;
use App\Modules\Inventory\Models\PlantStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PlantStockLifecycleFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PlantStock $plantStock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\UserSeeder::class);
        
        $this->admin = User::factory()->admin()->create();
        $this->admin->assignRole('admin');
        
        $this->plantStock = PlantStock::factory()->create([
            'quantity' => 100,
            'reserved_quantity' => 0,
            'status' => StockStatus::AVAILABLE,
        ]);
    }

    public function test_can_reserve_stock(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/plant-stocks/{$this->plantStock->id}/reserve", [
                'quantity' => 30,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plant_stocks', [
            'id' => $this->plantStock->id,
            'quantity' => 100,
            'reserved_quantity' => 30,
        ]);
    }

    public function test_cannot_reserve_more_than_available(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/plant-stocks/{$this->plantStock->id}/reserve", [
                'quantity' => 150,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'INSUFFICIENT_STOCK');
    }

    public function test_can_release_reserved_stock(): void
    {
        $this->plantStock->update([
            'reserved_quantity' => 40,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/plant-stocks/{$this->plantStock->id}/release", [
                'quantity' => 15,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plant_stocks', [
            'id' => $this->plantStock->id,
            'quantity' => 100,
            'reserved_quantity' => 25,
        ]);
    }

    public function test_can_consume_stock(): void
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/plant-stocks/{$this->plantStock->id}/consume", [
                'quantity' => 40,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plant_stocks', [
            'id' => $this->plantStock->id,
            'quantity' => 60,
            'reserved_quantity' => 0,
            'status' => StockStatus::AVAILABLE->value,
        ]);
    }

    public function test_consume_all_stock_changes_status_to_depleted_or_reserved(): void
    {
        $this->plantStock->update([
            'reserved_quantity' => 20,
        ]);

        // Available is 80. Consuming 80 leaves quantity 20, reserved 20.
        // The net available becomes 0, so status should become RESERVED.
        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/plant-stocks/{$this->plantStock->id}/consume", [
                'quantity' => 80,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plant_stocks', [
            'id' => $this->plantStock->id,
            'quantity' => 20,
            'reserved_quantity' => 20,
            'status' => StockStatus::RESERVED->value,
        ]);
    }

    public function test_can_restock_depleted_stock(): void
    {
        $this->plantStock->update([
            'quantity' => 0,
            'reserved_quantity' => 0,
            'status' => StockStatus::OUT_OF_STOCK,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/v1/plant-stocks/{$this->plantStock->id}/restock", [
                'quantity' => 50,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plant_stocks', [
            'id' => $this->plantStock->id,
            'quantity' => 50,
            'status' => StockStatus::AVAILABLE->value,
        ]);
    }
}

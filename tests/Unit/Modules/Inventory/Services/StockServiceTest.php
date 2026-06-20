<?php

namespace Tests\Unit\Modules\Inventory\Services;

use App\Exceptions\InsufficientStockException;
use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\StockStatus;
use App\Modules\Inventory\Models\PlantStock;
use App\Modules\Inventory\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;
    private PlantStock $stock;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stockService = app(StockService::class);
        $this->user = User::factory()->create();
        $this->stock = PlantStock::factory()->create([
            'quantity' => 100,
            'reserved_quantity' => 20, // Available = 80
            'status' => StockStatus::AVAILABLE,
        ]);
    }

    public function test_consume_reduces_quantity_and_syncs_status(): void
    {
        $result = $this->stockService->consume($this->stock, 10, $this->user);

        $this->assertEquals(90, $result->quantity);
        $this->assertEquals(20, $result->reserved_quantity);
        $this->assertEquals(StockStatus::AVAILABLE, $result->status);
    }

    public function test_consume_throws_exception_if_insufficient_stock(): void
    {
        $this->expectException(InsufficientStockException::class);

        $this->stockService->consume($this->stock, 90, $this->user); // Only 80 available
    }

    public function test_consume_all_available_stock_changes_status_to_reserved(): void
    {
        // If we consume the remaining 80 available, quantity becomes 20, reserved is 20
        $result = $this->stockService->consume($this->stock, 80, $this->user);

        $this->assertEquals(20, $result->quantity);
        $this->assertEquals(20, $result->reserved_quantity);
        $this->assertEquals(StockStatus::RESERVED, $result->status);
    }

    public function test_reserve_increases_reserved_quantity(): void
    {
        $result = $this->stockService->reserve($this->stock, 30, $this->user);

        $this->assertEquals(100, $result->quantity);
        $this->assertEquals(50, $result->reserved_quantity);
    }

    public function test_reserve_throws_exception_if_insufficient_stock(): void
    {
        $this->expectException(InsufficientStockException::class);

        $this->stockService->reserve($this->stock, 90, $this->user); // Only 80 available
    }

    public function test_release_decreases_reserved_quantity(): void
    {
        $result = $this->stockService->release($this->stock, 10, $this->user);

        $this->assertEquals(100, $result->quantity);
        $this->assertEquals(10, $result->reserved_quantity);
    }

    public function test_release_does_not_decrease_reserved_below_zero(): void
    {
        $result = $this->stockService->release($this->stock, 50, $this->user); // Trying to release more than 20

        $this->assertEquals(100, $result->quantity);
        $this->assertEquals(0, $result->reserved_quantity);
    }

    public function test_restock_increases_quantity_and_syncs_status(): void
    {
        $outOfStock = PlantStock::factory()->outOfStock()->create();

        $result = $this->stockService->restock($outOfStock, 50, $this->user);

        $this->assertEquals(50, $result->quantity);
        $this->assertEquals(StockStatus::AVAILABLE, $result->status);
    }
}

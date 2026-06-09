<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\StockStatus;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantStock>
 */
class PlantStockFactory extends Factory
{
    protected $model = PlantStock::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(10, 100);
        $data = [
            'plant_sample_id' => PlantSample::factory(),
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'status' => StockStatus::AVAILABLE,
        ];
        
        if (config('database.default') === 'sqlite') {
            $data['plant_species_id'] = \App\Modules\Inventory\Models\PlantSpecies::factory();
            $data['plant_variety_id'] = \App\Modules\Inventory\Models\PlantVariety::factory();
        }
        
        return $data;
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => StockStatus::AVAILABLE,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'quantity' => 0,
            'status' => StockStatus::OUT_OF_STOCK,
        ]);
    }

    public function fullyReserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'reserved_quantity' => $attributes['quantity'] ?? 10,
            'status' => StockStatus::RESERVED,
        ]);
    }
}

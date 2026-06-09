<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Inventory\Models\PlantSpecies;
use App\Modules\Inventory\Models\PlantVariety;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlantVarietyFactory extends Factory
{
    protected $model = PlantVariety::class;

    public function definition(): array
    {
        return [
            'plant_species_id' => PlantSpecies::factory(),
            'name' => $this->faker->words(2, true),
            'variety_code' => 'VAR-' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->sentence(),
        ];
    }
}

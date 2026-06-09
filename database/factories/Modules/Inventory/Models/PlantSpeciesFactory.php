<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Inventory\Models\PlantSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlantSpeciesFactory extends Factory
{
    protected $model = PlantSpecies::class;

    public function definition(): array
    {
        return [
            'scientific_name' => $this->faker->words(2, true),
            'common_name' => $this->faker->word(),
            'family' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}

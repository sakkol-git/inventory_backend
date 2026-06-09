<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\ChemicalCategory;
use App\Modules\Inventory\Enums\DangerLevel;
use App\Modules\Inventory\Models\Chemical;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chemical>
 */
class ChemicalFactory extends Factory
{
    protected $model = Chemical::class;

    public function definition(): array
    {
        return [
            'common_name' => $this->faker->words(3, true),
            'chemical_code' => 'CH-' . $this->faker->unique()->numberBetween(1000, 9999),
            'category' => $this->faker->randomElement(ChemicalCategory::cases()),
            'quantity' => $this->faker->numberBetween(10, 100),
            'storage_location' => $this->faker->word() . ' Cabinet',
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'danger_level' => $this->faker->randomElement(DangerLevel::cases()),
            'safety_measures' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'quantity' => $this->faker->numberBetween(1, 10),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'quantity' => 0,
        ]);
    }
}

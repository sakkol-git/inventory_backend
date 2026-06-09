<?php

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Inventory\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'equipment_name' => $this->faker->words(3, true),
            'equipment_code' => $this->faker->unique()->bothify('EQ-####'),
            'category' => $this->faker->randomElement(\App\Modules\Inventory\Enums\EquipmentCategory::cases()),
            'status' => \App\Modules\Inventory\Enums\EquipmentStatus::AVAILABLE,
            'condition' => \App\Modules\Inventory\Enums\EquipmentCondition::GOOD,
            'location' => $this->faker->word() . ' Lab',
            'manufacturer' => $this->faker->company(),
            'model_name' => $this->faker->word() . '-' . $this->faker->randomNumber(4),
            'serial_number' => $this->faker->uuid(),
            'purchase_date' => $this->faker->date(),
            'purchase_price' => $this->faker->randomFloat(2, 100, 5000),
            'description' => $this->faker->sentence(),
        ];
    }
}

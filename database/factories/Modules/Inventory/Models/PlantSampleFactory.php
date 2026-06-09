<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Enums\LabLocation;
use App\Modules\Inventory\Enums\SampleStatus;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantVariety;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlantSampleFactory extends Factory
{
    protected $model = PlantSample::class;

    public function definition(): array
    {
        return [
            'plant_variety_id' => PlantVariety::factory(),
            'user_id' => User::factory(),
            'sample_name' => $this->faker->words(3, true),
            'sample_code' => 'SMP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'department' => $this->faker->word(),
            'origin_location' => $this->faker->city(),
            'brought_at' => $this->faker->date(),
            'lab_location' => $this->faker->randomElement(LabLocation::cases()),
            'status' => $this->faker->randomElement(SampleStatus::cases()),
            'description' => $this->faker->sentence(),
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}

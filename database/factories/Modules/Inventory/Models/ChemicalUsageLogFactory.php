<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\ChemicalUsageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChemicalUsageLog>
 */
class ChemicalUsageLogFactory extends Factory
{
    protected $model = ChemicalUsageLog::class;

    public function definition(): array
    {
        return [
            'chemical_id' => Chemical::factory(),
            'user_id' => User::factory(),
            'quantity_used' => $this->faker->randomFloat(2, 0.1, 10.0),
            'unit' => $this->faker->randomElement(['ml', 'g', 'mg', 'l']),
            'purpose' => $this->faker->sentence(),
            'experiment_name' => $this->faker->words(3, true),
            'used_at' => $this->faker->dateTimeThisMonth(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

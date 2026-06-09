<?php

namespace Database\Factories\Modules\Inventory\Models;

use App\Modules\Inventory\Models\BorrowRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BorrowRecord>
 */
class BorrowRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = BorrowRecord::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Modules\Core\Models\User::factory(),
            'borrowable_type' => 'equipment', // Default morph mapping
            'borrowable_id' => \App\Modules\Inventory\Models\Equipment::factory(),
            'quantity' => 1,
            'status' => \App\Modules\Inventory\Enums\BorrowStatus::PENDING,
            'notes' => $this->faker->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Modules\Inventory\Enums\BorrowStatus::APPROVED,
            'reviewed_by' => \App\Modules\Core\Models\User::factory()->admin(),
            'reviewed_at' => now(),
            'due_at' => now()->addDays(7),
        ]);
    }

    public function borrowed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Modules\Inventory\Enums\BorrowStatus::BORROWED,
            'reviewed_by' => \App\Modules\Core\Models\User::factory()->admin(),
            'reviewed_at' => now()->subDays(1),
            'borrowed_at' => now(),
            'due_at' => now()->addDays(7),
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Modules\Inventory\Enums\BorrowStatus::RETURNED,
            'reviewed_by' => \App\Modules\Core\Models\User::factory()->admin(),
            'reviewed_at' => now()->subDays(5),
            'borrowed_at' => now()->subDays(4),
            'due_at' => now()->addDays(3),
            'returned_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Modules\Inventory\Enums\BorrowStatus::OVERDUE,
            'reviewed_by' => \App\Modules\Core\Models\User::factory()->admin(),
            'reviewed_at' => now()->subDays(10),
            'borrowed_at' => now()->subDays(9),
            'due_at' => now()->subDays(2),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Modules\Inventory\Enums\BorrowStatus::REJECTED,
            'reviewed_by' => \App\Modules\Core\Models\User::factory()->admin(),
            'reviewed_at' => now(),
            'rejected_reason' => $this->faker->sentence(),
        ]);
    }
}

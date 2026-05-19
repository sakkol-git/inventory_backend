<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => 'student',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
        ]);
    }

    public function labManager(): static
    {
        return $this->state(fn () => [
            'role' => 'lab_manager',
        ]);
    }

    public function student(): static
    {
        return $this->state(fn () => [
            'role' => 'student',
        ]);
    }

    public function unverified(): static
    {
        return $this;
    }
}

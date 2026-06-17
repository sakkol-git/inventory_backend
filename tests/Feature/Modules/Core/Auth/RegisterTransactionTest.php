<?php

namespace Tests\Feature\Modules\Core\Auth;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Mockery;

class RegisterTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_rolls_back_if_role_assignment_fails()
    {
        // Mock the Role model to throw an exception when firstOrCreate is called
        // To accurately test DB transaction rollback, we listen for the Role creation event and throw
        \Illuminate\Support\Facades\Event::listen('eloquent.creating: Spatie\Permission\Models\Role', function () {
            throw new \Exception('Simulated role creation failure');
        });

        $payload = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'UnCompromisedPass!@123',
            'password_confirmation' => 'UnCompromisedPass!@123',
        ];

        try {
            $this->postJson('/api/v1/auth/register', $payload);
        } catch (\Exception $e) {
            // Expecting an exception to bubble up during the test due to our mock
        }

        // Assert no user was created because the transaction rolled back
        $this->assertDatabaseMissing('users', [
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_register_successfully_creates_user()
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'UnCompromisedPass!@123',
            'password_confirmation' => 'UnCompromisedPass!@123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);
    }
}

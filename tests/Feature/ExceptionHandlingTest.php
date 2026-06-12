<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_exception_returns_standard_contract(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'error',
                'code',
                'message',
                'details',
                'correlation_id',
                'timestamp',
            ])
            ->assertJson([
                'success' => false,
                'error' => 'AuthenticationException',
                'code' => 'UNAUTHENTICATED',
                'message' => 'Unauthenticated',
            ]);
    }

    public function test_not_found_http_exception_returns_standard_contract(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'error',
                'code',
                'message',
                'details',
                'correlation_id',
                'timestamp',
            ])
            ->assertJson([
                'success' => false,
                'error' => 'NotFoundHttpException',
                'code' => 'ENDPOINT_NOT_FOUND',
                'message' => 'Endpoint not found.',
            ]);
    }

    public function test_correlation_id_is_propagated_to_error_responses(): void
    {
        $response = $this->withHeader('X-Request-Id', 'test-uuid-1234')
            ->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404)
            ->assertJson([
                'correlation_id' => 'test-uuid-1234',
            ]);
    }

    public function test_forbidden_exception_returns_standard_contract(): void
    {
        \Illuminate\Support\Facades\Route::get('/api/v1/test-forbidden', function () {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You do not have permission.');
        });

        $response = $this->getJson('/api/v1/test-forbidden');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'error',
                'code',
                'message',
                'details',
                'correlation_id',
                'timestamp',
            ])
            ->assertJson([
                'success' => false,
                'code' => 'FORBIDDEN',
            ]);
    }
}

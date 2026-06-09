<?php

namespace Tests\Feature\Modules\Inventory;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\ChemicalUsageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChemicalUsageFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $student;
    private Chemical $chemical;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\UserSeeder::class);
        
        $this->student = User::factory()->create(['role' => 'student']);
        $this->student->assignRole('student');
        
        $this->chemical = Chemical::factory()->create([
            'quantity' => 100,
        ]);
    }

    public function test_student_can_log_chemical_usage(): void
    {
        $response = $this->actingAs($this->student, 'api')
            ->postJson('/api/v1/chemical-usage-logs/use', [
                'chemical_id' => $this->chemical->id,
                'quantity_used' => 10,
                'unit' => 'ml',
                'purpose' => 'Titration experiment',
                'experiment_name' => 'Acid-Base Titration',
                'used_at' => now()->toIso8601String(),
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chemical_usage_logs', [
            'chemical_id' => $this->chemical->id,
            'user_id' => $this->student->id,
            'quantity_used' => 10,
            'purpose' => 'Titration experiment',
        ]);
    }

    public function test_logging_chemical_usage_reduces_inventory(): void
    {
        $initialQuantity = $this->chemical->quantity;
        $usageQuantity = 15;

        $response = $this->actingAs($this->student, 'api')
            ->postJson('/api/v1/chemical-usage-logs/use', [
                'chemical_id' => $this->chemical->id,
                'quantity_used' => $usageQuantity,
                'unit' => 'ml',
                'purpose' => 'Chemistry lab',
                'used_at' => now()->toIso8601String(),
            ]);

        $response->assertStatus(201);
        
        $this->chemical->refresh();
        $this->assertEquals($initialQuantity - $usageQuantity, $this->chemical->quantity);
    }

    public function test_cannot_log_usage_exceeding_inventory(): void
    {
        $response = $this->actingAs($this->student, 'api')
            ->postJson('/api/v1/chemical-usage-logs/use', [
                'chemical_id' => $this->chemical->id,
                'quantity_used' => 200, // available is 100
                'unit' => 'ml',
                'purpose' => 'Large scale reaction',
                'used_at' => now()->toIso8601String(),
            ]);

        $response->assertStatus(422);
    }

    public function test_usage_logging_validation_requires_purpose(): void
    {
        $response = $this->actingAs($this->student, 'api')
            ->postJson('/api/v1/chemical-usage-logs/use', [
                'chemical_id' => $this->chemical->id,
                'quantity_used' => 10,
                'used_at' => now()->toIso8601String(),
                // missing purpose
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'errors' => [
                        'purpose'
                    ]
                ]
            ]);
    }
}

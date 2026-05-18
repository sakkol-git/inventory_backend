<?php

declare(strict_types=1);

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Concerns\EscapesSearchTerm;
use App\Modules\Core\Http\Controllers\Controller;
// use App\Modules\Business\Models\Client;
// use App\Modules\Business\Models\Contract;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantSpecies;
use App\Modules\Inventory\Models\PlantStock;
use App\Modules\Inventory\Models\PlantVariety;
// use App\Modules\Research\Models\Experiment;
// use App\Modules\Research\Models\Protocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UX-04: Global search across all entity types.
 *
 * GET /api/v1/search?q=term&limit=5
 */
class SearchController extends Controller
{
    use EscapesSearchTerm;

    private const MAX_PER_TYPE = 5;

    /**
     * Searchable models mapped to their display config.
     */
    private function searchableModels(): array
    {
        return [
            [
                'model' => PlantSpecies::class,
                'type' => 'plant_species',
                'label' => 'Plant Species',
                'fields' => ['common_name', 'scientific_name', 'species_code'],
                'display' => fn ($m) => $m->common_name ?: $m->scientific_name,
                'subtitle' => fn ($m) => $m->scientific_name,
                'url' => fn ($m) => "/inventory/plant-species/{$m->id}",
            ],
            [
                'model' => PlantVariety::class,
                'type' => 'plant_variety',
                'label' => 'Plant Varieties',
                'fields' => ['name', 'variety_code'],
                'display' => fn ($m) => $m->name,
                'subtitle' => fn ($m) => $m->variety_code,
                'url' => fn ($m) => "/inventory/plant-varieties/{$m->id}",
            ],
            [
                'model' => PlantSample::class,
                'type' => 'plant_sample',
                'label' => 'Plant Samples',
                'fields' => ['sample_name', 'sample_code'],
                'display' => fn ($m) => $m->sample_name,
                'subtitle' => fn ($m) => $m->sample_code,
                'url' => fn ($m) => "/inventory/plant-samples/{$m->id}",
            ],
            [
                'model' => PlantStock::class,
                'type' => 'plant_stock',
                'label' => 'Plant Stock',
                'fields' => ['stock_code'],
                'display' => fn ($m) => $m->stock_code ?? "Stock #{$m->id}",
                'subtitle' => fn ($m) => "Qty: {$m->quantity}",
                'url' => fn ($m) => "/inventory/plant-stock/{$m->id}",
            ],
            [
                'model' => Chemical::class,
                'type' => 'chemical',
                'label' => 'Chemicals',
                'fields' => ['common_name', 'chemical_code', 'cas_number'],
                'display' => fn ($m) => $m->common_name,
                'subtitle' => fn ($m) => $m->chemical_code,
                'url' => fn ($m) => "/inventory/chemicals/{$m->id}",
            ],
            [
                'model' => Equipment::class,
                'type' => 'equipment',
                'label' => 'Equipment',
                'fields' => ['equipment_name', 'equipment_code', 'serial_number'],
                'display' => fn ($m) => $m->equipment_name,
                'subtitle' => fn ($m) => $m->equipment_code,
                'url' => fn ($m) => "/inventory/equipment/{$m->id}",
            ],
        ];
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['integer', 'min:1', 'max:10'],
        ]);

        $term = strtolower($this->escapeLike($request->input('q')));
        $limit = (int) $request->input('limit', self::MAX_PER_TYPE);
        $results = [];

        foreach ($this->searchableModels() as $config) {
            $query = $config['model']::query();

            $query->where(function ($q) use ($config, $term) {
                foreach ($config['fields'] as $i => $field) {
                    $method = $i === 0 ? 'whereRaw' : 'orWhereRaw';
                    $q->$method('LOWER('.$field.') LIKE ?', ["%{$term}%"]);
                }
            });

            $models = $query->limit($limit)->get();

            if ($models->isNotEmpty()) {
                $results[] = [
                    'type' => $config['type'],
                    'label' => $config['label'],
                    'items' => $models->map(fn ($m) => [
                        'id' => $m->id,
                        'title' => ($config['display'])($m),
                        'subtitle' => ($config['subtitle'])($m),
                        'url' => ($config['url'])($m),
                    ])->values()->all(),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'meta' => [
                'query' => $term,
                'total_results' => collect($results)->sum(fn ($g) => count($g['items'])),
            ],
        ]);
    }
}

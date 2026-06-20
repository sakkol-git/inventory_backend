<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class AdjustPlantStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller via policies
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}

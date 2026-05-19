<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Requests\BorrowRecord;

use App\Modules\Core\Concerns\HasImageValidation;
use App\Modules\Inventory\Models\Chemical;
use App\Modules\Inventory\Models\Equipment;
use App\Modules\Inventory\Models\PlantSample;
use App\Modules\Inventory\Models\PlantStock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBorrowRecordRequest extends FormRequest
{
    use HasImageValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller via policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'borrowable_type' => [
                'required',
                Rule::in(['equipment', 'chemical', 'plant_stock', 'plant_sample']),
            ],
            'borrowable_id' => [
                'required',
                'integer',
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'due_at' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('borrowable_type')) {
            $this->merge(['borrowable_type' => 'equipment']);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $type = (string) $this->input('borrowable_type', 'equipment');
            $id = (int) $this->input('borrowable_id');
            $quantity = (int) $this->input('quantity', 1);

            $map = [
                'equipment' => Equipment::class,
                'chemical' => Chemical::class,
                'plant_stock' => PlantStock::class,
                'plant_sample' => PlantSample::class,
            ];

            if (! isset($map[$type])) {
                $v->errors()->add('borrowable_type', 'Unsupported borrowable type.');

                return;
            }

            $borrowable = $map[$type]::query()->whereNull('deleted_at')->find($id);

            if (! $borrowable) {
                $v->errors()->add('borrowable_id', 'The selected item does not exist.');

                return;
            }

            if ($type === 'equipment') {
                if ($quantity !== 1) {
                    $v->errors()->add('quantity', 'Equipment can only be borrowed one at a time.');
                }

                if (! $borrowable->is_borrowable) {
                    $v->errors()->add('borrowable_id', 'The selected equipment is not available for borrowing.');
                }
            }

            if ($type === 'chemical') {
                if ($borrowable->is_expired) {
                    $v->errors()->add('borrowable_id', 'The selected chemical is expired.');
                }

                if ($borrowable->quantity < $quantity) {
                    $v->errors()->add('quantity', 'Insufficient chemical stock available.');
                }
            }

            if ($type === 'plant_stock' && $borrowable->available_quantity < $quantity) {
                $v->errors()->add('quantity', 'Insufficient plant stock available.');
            }

            if ($type === 'plant_sample' && $borrowable->stock_quantity !== null && $borrowable->stock_quantity < $quantity) {
                $v->errors()->add('quantity', 'Insufficient plant sample quantity available.');
            }
        });
    }
}

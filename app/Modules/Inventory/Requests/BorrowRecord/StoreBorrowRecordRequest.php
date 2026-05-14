<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Requests\BorrowRecord;

use App\Modules\Core\Concerns\HasImageValidation;
use App\Modules\Inventory\Models\Equipment;
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
            'borrowable_id' => [
                'required',
                'integer',
                Rule::exists('equipment', 'id')->whereNull('deleted_at'),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'due_at' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $equipment = Equipment::find($this->input('borrowable_id'));

            if ($equipment && ! $equipment->is_borrowable) {
                $v->errors()->add(
                    'borrowable_id',
                    'The selected equipment is not available for borrowing.'
                );
            }
        });
    }
}

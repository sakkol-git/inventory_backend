<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Requests\UserDocument;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled via policies in controller
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
            'file_type' => ['nullable', 'string', 'max:50', 'in:document,pdf,image,certificate,other'],
            'description' => ['nullable', 'string'],
            'achievement_id' => ['nullable', 'exists:achievements,id'],
        ];
    }
}

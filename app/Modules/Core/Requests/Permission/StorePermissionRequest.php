<?php

declare(strict_types=1);

namespace App\Modules\Core\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate::authorize('manage-roles') is checked in the controller.
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
        ];
    }
}

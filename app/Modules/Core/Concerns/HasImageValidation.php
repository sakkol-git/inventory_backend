<?php

declare(strict_types=1);

namespace App\Modules\Core\Concerns;

/**
 * HasImageValidation — shared validation rules for image upload fields.
 *
 * Provides a reusable set of validation rules for:
 *  • `image`     — uploaded file (jpg, jpeg, png, webp; max 2 MB)
 *  • `image_url` — external URL
 */
trait HasImageValidation
{
    /**
     * Validation rules for image fields.
     *
     * @return array<string, array<mixed>>
     */
    protected function imageRules(): array
    {
        return [
            'image' => [
                'nullable',
                'file',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                        $clientMime = $value->getClientMimeType();
                        $clientExt = strtolower($value->getClientOriginalExtension());
                        
                        if (!in_array($clientMime, $allowed) && !in_array($clientExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $fail('The image field must be a file of type: jpg, jpeg, png, webp.');
                        }
                    }
                },
            ],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'profile_image_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}

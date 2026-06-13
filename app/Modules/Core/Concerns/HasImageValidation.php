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
                function ($attribute, $value, $fail) {
                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
                        // If it's literally not a file object, it's either corrupted or JSON.
                        $fail('The image must be a valid file object. Received type: ' . gettype($value));
                        return;
                    }
                    
                    if (!$value->isValid()) {
                        // Expose the precise PHP UPLOAD_ERR code (1=INI_SIZE, 3=PARTIAL, 6=NO_TMP_DIR, 7=CANT_WRITE)
                        $fail('The image failed to upload to the server. PHP Upload Error Code: ' . $value->getError());
                        return;
                    }

                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $clientMime = $value->getClientMimeType();
                    $clientExt = strtolower($value->getClientOriginalExtension());
                    
                    if (!in_array($clientMime, $allowed) && !in_array($clientExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $fail('The image field must be a file of type: jpg, jpeg, png, webp.');
                    }
                },
                'max:2048',
            ],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'profile_image_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use Database\Factories\UserDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Modules\Core\Models\User;

class UserDocument extends Model
{
    /** @use HasFactory<UserDocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'user_documents';

    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::forceDeleting(function (UserDocument $document): void {
            if ($document->file_path) {
                Storage::disk('private')->delete($document->file_path);
            }
        });
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Models\User;
use Database\Factories\UserDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserDocument extends Model
{
    /** @use HasFactory<UserDocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'user_documents';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (UserDocument $document): void {
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

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}

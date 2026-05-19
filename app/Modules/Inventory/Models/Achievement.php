<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Core\Concerns\HasImageUpload;
use App\Modules\Core\Concerns\HasTransactions;
use App\Modules\Core\Models\User;
use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory, HasImageUpload, HasTransactions;

    protected $table = 'achievements';

    protected $fillable = [
        'achievement_name',
        'description',
        'criteria_type',
        'criteria_value',
        'icon',
        'image_url',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'criteria_value' => 'integer',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}

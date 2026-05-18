<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Achievement;

class AchievementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('achievements.view', 'api');
    }

    public function view(User $user, Achievement $achievement): bool
    {
        return $user->hasPermissionTo('achievements.view', 'api');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('achievements.create', 'api');
    }

    public function update(User $user, Achievement $achievement): bool
    {
        return $user->hasPermissionTo('achievements.edit', 'api');
    }

    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->hasPermissionTo('achievements.delete', 'api');
    }

    /**
     * Determine if the user can assign achievements to other users.
     * Typically only admins can assign achievements.
     */
    public function assign(User $user, Achievement $achievement): bool
    {
        return $user->hasPermissionTo('achievements.assign', 'api');
    }

    /**
     * Determine if the user can revoke achievements from users.
     * Typically only admins can revoke achievements.
     */
    public function revoke(User $user, Achievement $achievement): bool
    {
        return $user->hasPermissionTo('achievements.revoke', 'api');
    }
}

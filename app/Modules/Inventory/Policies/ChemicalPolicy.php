<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Chemical;

class ChemicalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('chemicals.view', 'api');
    }

    public function view(User $user, Chemical $chemical): bool
    {
        return $user->hasPermissionTo('chemicals.view', 'api');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('chemicals.create', 'api');
    }

    public function update(User $user, Chemical $chemical): bool
    {
        return $user->hasPermissionTo('chemicals.edit', 'api');
    }

    public function delete(User $user, Chemical $chemical): bool
    {
        return $user->hasPermissionTo('chemicals.delete', 'api');
    }
}

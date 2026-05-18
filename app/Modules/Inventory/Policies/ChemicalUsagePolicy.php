<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\ChemicalUsageLog;

class ChemicalUsagePolicy
{
    /**
     * Determine if the user can view any chemical usage logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('chemicals.view', 'api');
    }

    /**
     * Determine if the user can view a specific chemical usage log.
     */
    public function view(User $user, ChemicalUsageLog $log): bool
    {
        return $user->hasPermissionTo('chemicals.view', 'api');
    }

    /**
     * Determine if the user can create a chemical usage log.
     * Only users with 'chemicals.create' permission can record chemical usage.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('chemicals.create', 'api');
    }

    /**
     * Determine if the user can update a chemical usage log.
     * Typically only admins can edit usage logs.
     */
    public function update(User $user, ChemicalUsageLog $log): bool
    {
        return $user->hasPermissionTo('chemicals.edit', 'api');
    }

    /**
     * Determine if the user can delete a chemical usage log.
     * Typically only admins can delete usage logs (audit trail).
     */
    public function delete(User $user, ChemicalUsageLog $log): bool
    {
        return $user->hasPermissionTo('chemicals.delete', 'api');
    }
}

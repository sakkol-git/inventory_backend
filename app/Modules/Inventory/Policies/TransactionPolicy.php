<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Transaction;

class TransactionPolicy
{
    /**
     * Determine if the user can view any transactions.
     * Only users with 'transactions.view' permission can see all transactions.
     */
    /**
     * Determine if the user can view a specific transaction.
     * Users can view their own transactions or admins can view any.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // Admins can view any transaction
        if ($user->hasPermissionTo('transactions.view', 'api')) {
            return true;
        }

        // Users can only view transactions they initiated
        return $user->id === $transaction->user_id;
    }

    /**
     * Transactions are typically created automatically by the system,
     * so this policy prevents direct user creation.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Transactions should be immutable audit trails, so updates are not allowed.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return false;
    }

    /**
     * Transactions should be immutable audit trails, so deletion is restricted.
     * Only super-admins with explicit permission can delete transactions.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->hasPermissionTo('transactions.delete', 'api');
    }
}

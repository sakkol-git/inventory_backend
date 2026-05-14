<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\BorrowRecord;
use App\Modules\Inventory\Models\ChemicalUsageLog;
use App\Modules\Inventory\Models\Transaction;
use Illuminate\Support\Collection;

class ProfileService
{
    /**
     * Get the full profile data for a user.
     */
    public function getProfile(User $user): array
    {
        $user->load('roles', 'permissions');

        return [
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'summary' => $this->buildSummary($user),
        ];
    }

    /**
     * Update the user's profile with validated data.
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    /**
     * Get user contributions (samples, transactions, chemical usage).
     */
    public function getContributions(User $user): array
    {
        return [
            'contributed_samples' => $user->contributedSamples()
                ->with('species')
                ->latest()
                ->take(20)
                ->get(),
            'recent_transactions' => Transaction::where('user_id', $user->id)
                ->with('transactionable')
                ->latest()
                ->take(20)
                ->get(),
            'chemical_usage' => ChemicalUsageLog::where('user_id', $user->id)
                ->with('chemical')
                ->latest('used_at')
                ->take(20)
                ->get(),
        ];
    }

    /**
     * Get earned achievements for a user.
     */
    public function getAchievements(User $user): Collection
    {
        return $user->achievements()->withPivot('earned_at')->get();
    }

    /**
     * Get user activity within a date range.
     */
    public function getActivity(User $user, string $from, string $to): array
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('transactionable')
            ->latest()
            ->paginate(20);

        $borrowHistory = BorrowRecord::where('user_id', $user->id)
            ->with('borrowable')
            ->latest()
            ->take(20)
            ->get();

        return [
            'period' => ['from' => $from, 'to' => $to],
            'transactions' => $transactions,
            'borrows' => $borrowHistory,
        ];
    }

    /**
     * Build summary statistics for a user.
     */
    public function buildSummary(User $user): array
    {
        return [
            'total_borrows' => BorrowRecord::where('user_id', $user->id)->count(),
            'active_borrows' => BorrowRecord::where('user_id', $user->id)->active()->count(),
            'overdue_borrows' => BorrowRecord::where('user_id', $user->id)->overdue()->count(),
            'total_transactions' => Transaction::where('user_id', $user->id)->count(),
            'chemical_usages' => ChemicalUsageLog::where('user_id', $user->id)->count(),
            'contributed_samples' => $user->contributedSamples()->count(),
            'achievements_earned' => $user->achievements()->count(),
            'documents_uploaded' => $user->documents()->count(),
        ];
    }
}
<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access-pos') || $user->hasPermissionTo('view-reports');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('access-pos');
    }

    public function void(User $user, Transaction $transaction): bool
    {
        return $user->hasPermissionTo('void-transaction') && $user->store_id === $transaction->store_id;
    }
}

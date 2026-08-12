<?php

namespace App\Policies;

use App\Models\CashierShift;
use App\Models\User;

class CashierShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access-pos') || $user->hasPermissionTo('view-reports');
    }

    public function open(User $user): bool
    {
        return $user->hasPermissionTo('access-pos');
    }

    public function close(User $user, CashierShift $shift): bool
    {
        return ($user->id === $shift->user_id || $user->hasPermissionTo('view-reports')) && $user->store_id === $shift->store_id;
    }
}

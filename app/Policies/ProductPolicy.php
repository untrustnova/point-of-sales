<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-products') || $user->hasPermissionTo('access-pos');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('manage-products') && $user->store_id === $product->store_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('manage-products') && $user->store_id === $product->store_id;
    }
}

<?php

namespace App\Policies;

use App\Models\MenuCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MenuCategoryPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->role === 'manager_properti';
    }

    public function view(User $user, MenuCategory $menuCategory): bool
    {
        return $user->property_id === $menuCategory->restaurant->property_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager_properti';
    }

    public function update(User $user, MenuCategory $menuCategory): bool
    {
        return $user->property_id === $menuCategory->restaurant->property_id;
    }

    public function delete(User $user, MenuCategory $menuCategory): bool
    {
        return $user->property_id === $menuCategory->restaurant->property_id;
    }
}
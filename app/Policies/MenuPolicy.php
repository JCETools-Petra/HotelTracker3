<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MenuPolicy
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

    public function view(User $user, Menu $menu): bool
    {
        return $user->property_id === $menu->menuCategory->restaurant->property_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager_properti';
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->property_id === $menu->menuCategory->restaurant->property_id;
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->property_id === $menu->menuCategory->restaurant->property_id;
    }
}
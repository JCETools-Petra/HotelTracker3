<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TablePolicy
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

    public function view(User $user, Table $table): bool
    {
        return $user->property_id === $table->restaurant->property_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager_properti';
    }

    public function update(User $user, Table $table): bool
    {
        return $user->property_id === $table->restaurant->property_id;
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->property_id === $table->restaurant->property_id;
    }
}
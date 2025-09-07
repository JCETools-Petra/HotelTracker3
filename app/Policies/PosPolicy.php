<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class PosPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }
        return null;
    }

    /**
     * Tentukan apakah pengguna dapat melihat POS restoran tertentu.
     */
    public function viewPos(User $user, Restaurant $restaurant): bool
    {
        if ($user->role === 'manager_properti') {
            return $user->property_id === $restaurant->property_id;
        }

        if ($user->role === 'restaurant') {
            return $user->restaurant_id === $restaurant->id;
        }

        return false; // Tolak peran lain secara default
    }
}
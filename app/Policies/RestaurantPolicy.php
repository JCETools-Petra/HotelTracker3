<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RestaurantPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Menambahkan 'owner' sesuai pola di aplikasi Anda
        if (in_array($user->role, ['admin', 'owner'])) {
            return true;
        }

        return null;
    }

    /**
     * Tentukan apakah pengguna dapat melihat POS restoran tertentu.
     * (METODE BARU DITAMBAHKAN DI SINI)
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


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'manager_properti';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->property_id === $restaurant->property_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'manager_properti';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->property_id === $restaurant->property_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->property_id === $restaurant->property_id;
    }
}
<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Restaurant;
use App\Policies\RestaurantPolicy;
use App\Models\MenuCategory;
use App\Policies\MenuCategoryPolicy;
use App\Models\Table;
use App\Policies\TablePolicy;
use App\Models\Menu;
use App\Policies\MenuPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\DailyIncome' => 'App\Policies\DailyIncomePolicy',
        Restaurant::class => RestaurantPolicy::class,
        MenuCategory::class => MenuCategoryPolicy::class,
        Table::class => TablePolicy::class,
        Menu::class => MenuPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate ini hanya akan memberikan izin 'true' jika peran pengguna
         * adalah 'admin' atau 'owner'. Peran 'pengurus' dan lainnya
         * akan mendapatkan 'false', sehingga hanya bisa melihat.
         */
        Gate::define('manage-data', function (User $user) {
            return in_array($user->role, ['admin', 'owner']);
        });
    }
}
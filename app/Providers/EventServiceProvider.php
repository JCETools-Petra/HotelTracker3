<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Event dan Listener
use App\Events\OccupancyUpdated;
use App\Listeners\SendOccupancyUpdateNotification;
use App\Listeners\SendOccupancyUpdateWhatsApp;

// Model dan Observer
use App\Models\Reservation; 
use App\Observers\ReservationObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OccupancyUpdated::class => [
            //SendOccupancyUpdateNotification::class,
            SendOccupancyUpdateWhatsApp::class,
        ],
    ];

    /**
     * The model observers for your application.
     *
     * @var array
     */
    // ==========================================================
    // == BAGIAN YANG PERLU DITAMBAHKAN ADA DI SINI ==
    // ==========================================================
    protected $observers = [
        Reservation::class => [ReservationObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
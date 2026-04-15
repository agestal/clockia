<?php

namespace App\Providers;

use App\Events\BookingCreated;
use App\Listeners\SyncBookingToGoogleCalendar;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BookingCreated::class, SyncBookingToGoogleCalendar::class);

        Passport::tokensCan([
            'business:read' => 'Read business context information.',
            'services:read' => 'Read business services.',
            'services:write' => 'Create, update and delete business services.',
            'resources:read' => 'Read business resources.',
            'resources:write' => 'Create, update and delete business resources.',
            'availabilities:read' => 'Read business availabilities.',
            'availabilities:write' => 'Create, update and delete business availabilities.',
            'blocks:read' => 'Read business blocks.',
            'blocks:write' => 'Create, update and delete business blocks.',
            'reservations:read' => 'Read business reservations.',
            'reservations:write' => 'Create, update and delete business reservations.',
            'payments:read' => 'Read business payments.',
            'payments:write' => 'Create, update and delete business payments.',
        ]);

        Passport::tokensExpireIn(now()->addHours(8));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}

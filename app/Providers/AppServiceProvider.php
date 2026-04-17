<?php

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Events\BookingModified;
use App\Listeners\NotifyAdminBookingCancelled;
use App\Listeners\NotifyAdminBookingCreated;
use App\Listeners\NotifyAdminBookingModified;
use App\Listeners\SyncBookingToGoogleCalendar;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Event::listen(BookingCreated::class, NotifyAdminBookingCreated::class);
        Event::listen(BookingModified::class, SyncBookingToGoogleCalendar::class);
        Event::listen(BookingModified::class, NotifyAdminBookingModified::class);
        Event::listen(BookingCancelled::class, NotifyAdminBookingCancelled::class);

        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasFullAdminAccess() ? true : null;
        });

        Gate::define('manage-platform-admin', fn (User $user): bool => $user->hasFullAdminAccess());

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

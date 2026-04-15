<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Services\Integrations\GoogleCalendarSyncService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class SyncBookingToGoogleCalendar implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(
        private readonly GoogleCalendarSyncService $syncService,
    ) {}

    public function handle(BookingCreated $event): void
    {
        $this->syncService->syncBooking($event->booking);
    }
}

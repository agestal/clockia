<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Services\Notifications\AdminNotificationService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminBookingCreated implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(
        private readonly AdminNotificationService $notificationService,
    ) {}

    public function handle(BookingCreated $event): void
    {
        $this->notificationService->reservaNueva($event->booking);
    }
}

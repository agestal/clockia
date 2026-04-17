<?php

namespace App\Listeners;

use App\Events\BookingModified;
use App\Services\Notifications\AdminNotificationService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminBookingModified implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(
        private readonly AdminNotificationService $notificationService,
    ) {}

    public function handle(BookingModified $event): void
    {
        $this->notificationService->reservaModificada($event->booking, $event->changeSummary);
    }
}

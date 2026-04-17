<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Services\Notifications\AdminNotificationService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminBookingCancelled implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(
        private readonly AdminNotificationService $notificationService,
    ) {}

    public function handle(BookingCancelled $event): void
    {
        $this->notificationService->anulacionReserva($event->booking);
    }
}

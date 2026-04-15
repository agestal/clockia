<?php

namespace App\Jobs;

use App\Services\Integrations\GoogleCalendarImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportGoogleCalendarEventsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $businessId,
        public readonly ?int $daysAhead = null,
    ) {}

    public function handle(GoogleCalendarImportService $importService): void
    {
        $importService->importUpcomingEvents($this->businessId, $this->daysAhead);
    }
}

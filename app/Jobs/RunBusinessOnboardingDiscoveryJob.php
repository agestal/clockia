<?php

namespace App\Jobs;

use App\Models\BusinessOnboardingSession;
use App\Services\Onboarding\BusinessOnboardingDiscoveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunBusinessOnboardingDiscoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $sessionId,
    ) {}

    public function handle(BusinessOnboardingDiscoveryService $discoveryService): void
    {
        $session = BusinessOnboardingSession::query()->find($this->sessionId);

        if (! $session) {
            return;
        }

        try {
            $discoveryService->run($session);
        } catch (\Throwable $exception) {
            $session->update([
                'status' => BusinessOnboardingSession::STATUS_FAILED,
                'last_error' => $exception->getMessage(),
                'discovery_finished_at' => now(),
            ]);

            throw $exception;
        }
    }
}

<?php

namespace App\Tools;

use App\Services\PolicyResolver;
use App\Tools\Reservations\CheckBusinessHoursTool;
use App\Tools\Reservations\CreateQuoteTool;
use App\Tools\Reservations\GetArrivalInstructionsTool;
use App\Tools\Reservations\GetCancellationPolicyTool;
use App\Tools\Reservations\GetServiceDetailsTool;
use App\Tools\Reservations\ListBookableServicesTool;
use App\Tools\Reservations\SearchAvailabilityTool;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ToolRegistry::class, function ($app) {
            $registry = new ToolRegistry();
            $policyResolver = $app->make(PolicyResolver::class);

            $registry->register(new ListBookableServicesTool());
            $registry->register(new GetServiceDetailsTool($policyResolver));
            $registry->register(new CheckBusinessHoursTool());
            $registry->register(new SearchAvailabilityTool());
            $registry->register(new CreateQuoteTool($policyResolver));
            $registry->register(new GetCancellationPolicyTool($policyResolver));
            $registry->register(new GetArrivalInstructionsTool());

            return $registry;
        });
    }
}

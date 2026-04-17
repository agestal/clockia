<?php

namespace App\Tools;

use App\Tools\Reservations\CheckBusinessHoursTool;
use App\Tools\Reservations\CreateBookingTool;
use App\Tools\Reservations\CreateQuoteTool;
use App\Tools\Reservations\GetArrivalInstructionsTool;
use App\Tools\Reservations\GetCancellationPolicyTool;
use App\Tools\Reservations\GetServiceDetailsTool;
use App\Tools\Reservations\ListBookableServicesTool;
use App\Tools\Reservations\ModifyBookingTool;
use App\Tools\Reservations\RequestBookingCancellationTool;
use App\Tools\Reservations\SearchAvailabilityTool;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ToolRegistry::class, function ($app) {
            $registry = new ToolRegistry;

            $registry->register($app->make(ListBookableServicesTool::class));
            $registry->register($app->make(GetServiceDetailsTool::class));
            $registry->register($app->make(CheckBusinessHoursTool::class));
            $registry->register($app->make(SearchAvailabilityTool::class));
            $registry->register($app->make(CreateQuoteTool::class));
            $registry->register($app->make(CreateBookingTool::class));
            $registry->register($app->make(ModifyBookingTool::class));
            $registry->register($app->make(GetCancellationPolicyTool::class));
            $registry->register($app->make(GetArrivalInstructionsTool::class));
            $registry->register($app->make(RequestBookingCancellationTool::class));

            return $registry;
        });
    }
}

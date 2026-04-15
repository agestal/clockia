<?php

use App\Http\Controllers\Api\V1\AvailabilityController;
use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\GoogleCalendarController;
use App\Http\Controllers\Mcp\McpBridgeController;
use App\Http\Controllers\Widget\WidgetPublicController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['auth:api'])->group(function () {
    Route::prefix('businesses/{business}')->name('businesses.')->middleware(['business.access'])->group(function () {
        Route::get('services', [ServiceController::class, 'index'])
            ->middleware('scopes:services:read')
            ->name('services.index');
        Route::post('services', [ServiceController::class, 'store'])
            ->middleware('scopes:services:write')
            ->name('services.store');
        Route::get('services/{service}', [ServiceController::class, 'show'])
            ->middleware('scopes:services:read')
            ->name('services.show');
        Route::match(['put', 'patch'], 'services/{service}', [ServiceController::class, 'update'])
            ->middleware('scopes:services:write')
            ->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])
            ->middleware('scopes:services:write')
            ->name('services.destroy');

        Route::get('resources', [ResourceController::class, 'index'])
            ->middleware('scopes:resources:read')
            ->name('resources.index');
        Route::post('resources', [ResourceController::class, 'store'])
            ->middleware('scopes:resources:write')
            ->name('resources.store');
        Route::get('resources/{resource}', [ResourceController::class, 'show'])
            ->middleware('scopes:resources:read')
            ->name('resources.show');
        Route::match(['put', 'patch'], 'resources/{resource}', [ResourceController::class, 'update'])
            ->middleware('scopes:resources:write')
            ->name('resources.update');
        Route::delete('resources/{resource}', [ResourceController::class, 'destroy'])
            ->middleware('scopes:resources:write')
            ->name('resources.destroy');

        Route::get('availabilities', [AvailabilityController::class, 'index'])
            ->middleware('scopes:availabilities:read')
            ->name('availabilities.index');
        Route::post('availabilities', [AvailabilityController::class, 'store'])
            ->middleware('scopes:availabilities:write')
            ->name('availabilities.store');
        Route::get('availabilities/{availability}', [AvailabilityController::class, 'show'])
            ->middleware('scopes:availabilities:read')
            ->name('availabilities.show');
        Route::match(['put', 'patch'], 'availabilities/{availability}', [AvailabilityController::class, 'update'])
            ->middleware('scopes:availabilities:write')
            ->name('availabilities.update');
        Route::delete('availabilities/{availability}', [AvailabilityController::class, 'destroy'])
            ->middleware('scopes:availabilities:write')
            ->name('availabilities.destroy');

        Route::get('blocks', [BlockController::class, 'index'])
            ->middleware('scopes:blocks:read')
            ->name('blocks.index');
        Route::post('blocks', [BlockController::class, 'store'])
            ->middleware('scopes:blocks:write')
            ->name('blocks.store');
        Route::get('blocks/{block}', [BlockController::class, 'show'])
            ->middleware('scopes:blocks:read')
            ->name('blocks.show');
        Route::match(['put', 'patch'], 'blocks/{block}', [BlockController::class, 'update'])
            ->middleware('scopes:blocks:write')
            ->name('blocks.update');
        Route::delete('blocks/{block}', [BlockController::class, 'destroy'])
            ->middleware('scopes:blocks:write')
            ->name('blocks.destroy');
    });
});

Route::middleware(['auth:api'])->prefix('integrations/google')->group(function () {
    Route::get('connect', [GoogleCalendarController::class, 'connect']);
    Route::get('calendars', [GoogleCalendarController::class, 'calendars']);
    Route::post('calendars/select', [GoogleCalendarController::class, 'selectCalendars']);
    Route::post('import', [GoogleCalendarController::class, 'import']);
});

Route::get('integrations/google/callback', [GoogleCalendarController::class, 'callback']);

// ─── Widget público embebible ───
Route::prefix('widget')->name('widget.')->middleware(['throttle:60,1'])->group(function () {
    Route::prefix('businesses/{business}')->middleware(['widget.key'])->group(function () {
        Route::get('config', [WidgetPublicController::class, 'config'])->name('config');
        Route::get('availability/calendar', [WidgetPublicController::class, 'calendar'])->name('calendar');
        Route::get('availability/date', [WidgetPublicController::class, 'date'])->name('date');
        Route::post('availability/check', [WidgetPublicController::class, 'check'])->name('check');
        Route::post('bookings', [WidgetPublicController::class, 'book'])->name('book');
    });
});

// ─── MCP Bridge ───
Route::prefix('mcp')->name('mcp.')->middleware(\App\Http\Middleware\McpTokenAuth::class)->group(function () {
    Route::get('tools', [McpBridgeController::class, 'listTools'])->name('tools.list');
    Route::post('tools/execute', [McpBridgeController::class, 'executeTool'])->name('tools.execute');
    Route::get('businesses/{negocio}/profile', [McpBridgeController::class, 'getChatbotProfile'])->name('profile');
    Route::get('conversation/requirements', [McpBridgeController::class, 'getConversationRequirements'])->name('requirements');
    Route::post('conversation/confirmation', [McpBridgeController::class, 'buildConfirmationSummary'])->name('confirmation');
    Route::post('conversation/clarification', [McpBridgeController::class, 'buildClarificationQuestion'])->name('clarification');
});

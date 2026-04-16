<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\MissingScopeException;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\EnsureBusinessAccess;
use App\Http\Middleware\EnsureAdminPanelAccess;
use Laravel\Passport\Http\Middleware\CheckToken;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'business.access' => EnsureBusinessAccess::class,
            'admin.panel.access' => EnsureAdminPanelAccess::class,
            'scopes' => CheckToken::class,
            'scope' => CheckTokenForAnyScope::class,
            'widget.key' => \App\Http\Middleware\WidgetKeyAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, \Throwable $e) => $request->is('api/*'));

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'This action is unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (MissingScopeException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'The token is missing the required scope.',
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Resource not found.',
            ], Response::HTTP_NOT_FOUND);
        });
    })->create();

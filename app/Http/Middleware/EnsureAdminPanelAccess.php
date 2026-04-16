<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanelAccess
{
    public function __construct(
        private readonly AdminAccess $adminAccess,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $this->adminAccess->allowsAdminRoute($user, $routeName)) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permisos para acceder a esta sección.');
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (! $parameter instanceof Model) {
                continue;
            }

            if (! $this->adminAccess->canAccessModel($user, $parameter)) {
                abort(Response::HTTP_FORBIDDEN, 'No tienes permisos para acceder a este recurso.');
            }
        }

        return $next($request);
    }
}

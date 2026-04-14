<?php

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $business = $request->route('business');

        if (! $user || ! $business instanceof Negocio) {
            return $this->forbiddenResponse();
        }

        $hasAccess = $user->negocios()
            ->whereKey($business->getKey())
            ->exists();

        if (! $hasAccess) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    private function forbiddenResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'You do not have access to this business.',
        ], Response::HTTP_FORBIDDEN);
    }
}

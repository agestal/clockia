<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class McpTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.mcp.bridge_token');

        if (! $token || $token === '') {
            return response()->json(['error' => 'MCP bridge token not configured.'], 500);
        }

        $provided = $request->bearerToken();

        if ($provided !== $token) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}

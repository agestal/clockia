<?php

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WidgetKeyAuth
{
    public function handle(Request $request, Closure $next, string $feature = 'booking'): Response
    {
        $business = $request->route('business');

        if (! $business instanceof Negocio) {
            $businessId = is_numeric($business) ? (int) $business : null;
            $business = $businessId ? Negocio::find($businessId) : null;
            if ($business) {
                $request->route()->setParameter('business', $business);
            }
        }

        if (! $business instanceof Negocio) {
            return response()->json(['error' => 'Negocio no encontrado.'], 404);
        }

        if (! $business->activo) {
            return response()->json(['error' => 'El negocio no está activo.'], 403);
        }

        $enabled = match ($feature) {
            'chat' => (bool) $business->chat_widget_enabled,
            default => (bool) $business->widget_enabled,
        };

        if (! $enabled) {
            return response()->json(['error' => 'El widget no está activo para este negocio.'], 403);
        }

        $providedKey = $request->header('X-Widget-Key')
            ?: $request->query('widget_key')
            ?: $request->input('widget_key');

        if (! is_string($providedKey) || $providedKey === '') {
            return response()->json(['error' => 'Falta la clave pública del widget.'], 401);
        }

        if (! is_string($business->widget_public_key) || $business->widget_public_key === '') {
            return response()->json(['error' => 'Widget sin clave configurada.'], 403);
        }

        if (! hash_equals($business->widget_public_key, $providedKey)) {
            return response()->json(['error' => 'Clave de widget inválida.'], 401);
        }

        return $next($request);
    }
}

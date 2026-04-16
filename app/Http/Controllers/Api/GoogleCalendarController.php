<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Integrations\GoogleCalendarCalendarsRequest;
use App\Http\Requests\Api\Integrations\GoogleCalendarConnectRequest;
use App\Http\Requests\Api\Integrations\GoogleCalendarImportRequest;
use App\Http\Requests\Api\Integrations\GoogleCalendarSelectCalendarsRequest;
use App\Jobs\ImportGoogleCalendarEventsJob;
use App\Models\Negocio;
use App\Services\Integrations\GoogleCalendarAuthService;
use App\Services\Integrations\GoogleCalendarClient;
use App\Support\AdminAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GoogleCalendarController extends Controller
{
    public function __construct(
        private readonly GoogleCalendarAuthService $authService,
        private readonly GoogleCalendarClient $client,
        private readonly AdminAccess $adminAccess,
    ) {}

    public function connect(GoogleCalendarConnectRequest $request): JsonResponse|RedirectResponse
    {
        $business = $this->resolveBusiness($request, $request->integer('business_id'));
        $url = $this->authService->buildConnectUrl($business);

        if ($request->boolean('redirect', true)) {
            return redirect()->away($url);
        }

        return response()->json([
            'business_id' => $business->id,
            'authorization_url' => $url,
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $integration = $this->authService->handleCallback($validated['code'], $validated['state']);

        return response()->json([
            'message' => 'Google Calendar conectado correctamente.',
            'data' => [
                'business_id' => $integration->negocio_id,
                'provider' => $integration->proveedor,
                'enabled' => (bool) $integration->activo,
                'status' => $integration->estado,
            ],
        ]);
    }

    public function calendars(GoogleCalendarCalendarsRequest $request): JsonResponse
    {
        $business = $this->resolveBusiness($request, $request->integer('business_id'));
        $integration = $this->authService->connectedIntegration($business);

        if (! $integration) {
            return response()->json([
                'message' => 'El negocio aún no tiene Google Calendar conectado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $remoteCalendars = collect($this->client->listCalendars($this->authService->accessToken($integration)));
        $mappings = $integration->mapeosCalendario()->get()->keyBy('external_id');

        return response()->json([
            'data' => $remoteCalendars->map(function (array $calendar) use ($mappings) {
                $mapping = $mappings->get($calendar['id']);

                return [
                    'google_calendar_id' => $calendar['id'],
                    'summary' => $calendar['summary'] ?? null,
                    'timezone' => $calendar['timeZone'] ?? null,
                    'is_primary' => (bool) ($calendar['primary'] ?? false),
                    'selected' => (bool) ($mapping?->seleccionado ?? false),
                    'resource_id' => $mapping?->recurso_id,
                    'mapping_id' => $mapping?->id,
                    'background_color' => $calendar['backgroundColor'] ?? null,
                    'access_role' => $calendar['accessRole'] ?? null,
                ];
            })->values(),
        ]);
    }

    public function selectCalendars(GoogleCalendarSelectCalendarsRequest $request): JsonResponse
    {
        $business = $this->resolveBusiness($request, $request->integer('business_id'));
        $integration = $this->authService->connectedIntegration($business);

        if (! $integration) {
            return response()->json([
                'message' => 'El negocio aún no tiene Google Calendar conectado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $selected = $this->authService->mergeCalendarSelections($integration, $request->validated('calendars', []));

        return response()->json([
            'message' => 'Los calendarios de Google se han guardado correctamente.',
            'data' => $selected->map(fn ($calendar) => [
                'id' => $calendar->id,
                'google_calendar_id' => $calendar->external_id,
                'summary' => $calendar->nombre_externo,
                'timezone' => $calendar->timezone,
                'is_primary' => (bool) $calendar->es_primario,
                'selected' => (bool) $calendar->seleccionado,
                'resource_id' => $calendar->recurso_id,
            ])->values(),
        ]);
    }

    public function import(GoogleCalendarImportRequest $request): JsonResponse
    {
        $business = $this->resolveBusiness($request, $request->integer('business_id'));

        if (! $this->authService->connectedIntegration($business)) {
            return response()->json([
                'message' => 'El negocio aún no tiene Google Calendar conectado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ImportGoogleCalendarEventsJob::dispatch(
            $business->id,
            $request->validated('days_ahead')
        );

        return response()->json([
            'message' => 'La importación de Google Calendar se ha enviado a cola.',
            'data' => [
                'business_id' => $business->id,
                'days_ahead' => $request->validated('days_ahead') ?? (int) config('services.google_calendar.import_days', 30),
            ],
        ], Response::HTTP_ACCEPTED);
    }

    private function resolveBusiness(Request $request, int $businessId): Negocio
    {
        $business = $this->adminAccess
            ->accessibleBusinessesQuery($request->user())
            ->whereKey($businessId)
            ->first();

        abort_if($business === null, Response::HTTP_FORBIDDEN, 'No tienes acceso a este negocio.');

        return $business;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportGoogleCalendarEventsJob;
use App\Models\Integracion;
use App\Models\Negocio;
use App\Services\Integrations\GoogleCalendarAuthService;
use App\Services\Integrations\GoogleCalendarClient;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use JsonException;
use RuntimeException;
use Throwable;

class GoogleCalendarIntegrationController extends Controller
{
    public function __construct(
        private readonly GoogleCalendarAuthService $authService,
        private readonly GoogleCalendarClient $client,
    ) {}

    public function connect(Negocio $negocio): RedirectResponse
    {
        try {
            $this->authService->syncToggleState($negocio, true);

            return redirect()->away(
                $this->authService->buildConnectUrl(
                    $negocio,
                    route('admin.integraciones.google.callback')
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.negocios.edit', $negocio)
                ->with('error', $exception->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        try {
            $integration = $this->authService->handleCallback(
                $validated['code'],
                $validated['state'],
                route('admin.integraciones.google.callback')
            );

            $integration->forceFill([
                'activo' => true,
            ])->save();

            $calendarCount = $this->syncRemoteCalendars($integration->fresh(['cuentaActiva']));

            $message = $calendarCount > 0
                ? "Google Calendar conectado correctamente. Ya puedes revisar {$calendarCount} calendario(s)."
                : 'Google Calendar conectado correctamente.';

            return redirect()
                ->route('admin.negocios.edit', $integration->negocio_id)
                ->with('success', $message);
        } catch (Throwable $exception) {
            report($exception);

            $businessId = $this->resolveBusinessIdFromState($validated['state']);
            $redirect = $businessId !== null
                ? redirect()->route('admin.negocios.edit', $businessId)
                : redirect()->route('admin.negocios.index');

            return $redirect->with('error', $exception->getMessage());
        }
    }

    public function syncCalendars(Negocio $negocio): RedirectResponse
    {
        try {
            $integration = $this->authService->connectedIntegration($negocio);

            if (! $integration) {
                throw new RuntimeException('Primero conecta una cuenta de Google Calendar.');
            }

            $calendarCount = $this->syncRemoteCalendars($integration);

            return redirect()
                ->route('admin.negocios.edit', $negocio)
                ->with(
                    'success',
                    $calendarCount > 0
                        ? "Calendarios actualizados correctamente ({$calendarCount})."
                        : 'La cuenta conectada no devolvió calendarios disponibles.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.negocios.edit', $negocio)
                ->with('error', $exception->getMessage());
        }
    }

    public function updateCalendars(Request $request, Negocio $negocio): RedirectResponse
    {
        $integration = $this->authService->connectedIntegration($negocio);

        if (! $integration) {
            return redirect()
                ->route('admin.negocios.edit', $negocio)
                ->with('error', 'Primero conecta una cuenta de Google Calendar.');
        }

        $request->validate([
            'resource_ids' => ['nullable', 'array'],
            'resource_ids.*' => [
                'nullable',
                'integer',
                Rule::exists('recursos', 'id')->where(fn ($query) => $query->where('negocio_id', $negocio->id)),
            ],
            'selected' => ['nullable', 'array'],
        ]);

        $selectedIds = collect(array_keys($request->input('selected', [])))
            ->map(static fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $integration->mapeosCalendario()
            ->orderByDesc('es_primario')
            ->orderBy('nombre_externo')
            ->get()
            ->each(function ($mapping) use ($request, $selectedIds) {
                $resourceId = $request->input("resource_ids.{$mapping->id}");

                $mapping->forceFill([
                    'seleccionado' => in_array($mapping->id, $selectedIds, true),
                    'recurso_id' => $resourceId !== null && $resourceId !== '' ? (int) $resourceId : null,
                    'activo' => true,
                ])->save();
            });

        return redirect()
            ->route('admin.negocios.edit', $negocio)
            ->with('success', 'La selección de calendarios se ha guardado correctamente.');
    }

    public function import(Request $request, Negocio $negocio): RedirectResponse
    {
        $validated = $request->validate([
            'days_ahead' => ['nullable', 'integer', 'min:1', 'max:180'],
        ]);

        if (! $this->authService->connectedIntegration($negocio)) {
            return redirect()
                ->route('admin.negocios.edit', $negocio)
                ->with('error', 'Primero conecta una cuenta de Google Calendar.');
        }

        ImportGoogleCalendarEventsJob::dispatch(
            $negocio->id,
            $validated['days_ahead'] ?? null
        );

        return redirect()
            ->route('admin.negocios.edit', $negocio)
            ->with('success', 'La importación de Google Calendar se ha enviado a cola.');
    }

    private function syncRemoteCalendars(Integracion $integration): int
    {
        $remoteCalendars = collect($this->client->listCalendars($this->authService->accessToken($integration)));
        $existingMappings = $integration->mapeosCalendario()->get()->keyBy('external_id');
        $hasSelectedCalendars = $existingMappings->contains(fn ($mapping) => (bool) $mapping->seleccionado);

        $payloads = $remoteCalendars->map(function (array $calendar) use ($existingMappings, $hasSelectedCalendars) {
            $mapping = $existingMappings->get($calendar['id']);

            return [
                'google_calendar_id' => $calendar['id'],
                'summary' => $calendar['summary'] ?? null,
                'timezone' => $calendar['timeZone'] ?? null,
                'is_primary' => (bool) ($calendar['primary'] ?? false),
                'selected' => (bool) ($mapping?->seleccionado ?? (! $hasSelectedCalendars && (bool) ($calendar['primary'] ?? false))),
                'resource_id' => $mapping?->recurso_id,
                'background_color' => $calendar['backgroundColor'] ?? null,
                'access_role' => $calendar['accessRole'] ?? null,
            ];
        })->values()->all();

        $this->authService->mergeCalendarSelections($integration, $payloads);

        return count($payloads);
    }

    private function resolveBusinessIdFromState(string $state): ?int
    {
        try {
            $payload = json_decode(Crypt::decryptString($state), true, 512, JSON_THROW_ON_ERROR);

            return isset($payload['business_id']) ? (int) $payload['business_id'] : null;
        } catch (DecryptException|JsonException) {
            return null;
        }
    }
}

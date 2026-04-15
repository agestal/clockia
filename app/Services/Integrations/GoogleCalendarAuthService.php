<?php

namespace App\Services\Integrations;

use App\Models\Integracion;
use App\Models\IntegracionCuenta;
use App\Models\IntegracionMapeo;
use App\Models\Negocio;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class GoogleCalendarAuthService
{
    public function __construct(
        private readonly GoogleCalendarClient $client,
    ) {}

    public function buildConnectUrl(Negocio $negocio, ?string $redirectUri = null): string
    {
        return $this->client->buildAuthorizationUrl(
            $this->buildState($negocio),
            $redirectUri ?? $this->redirectUri()
        );
    }

    public function handleCallback(string $code, string $state, ?string $redirectUri = null): Integracion
    {
        $payload = $this->decodeState($state);
        $negocio = Negocio::query()->findOrFail((int) $payload['business_id']);
        $integracion = $this->ensureIntegration($negocio);
        $tokens = $this->client->exchangeCodeForTokens($code, $redirectUri ?? $this->redirectUri());
        $calendars = $this->client->listCalendars((string) ($tokens['access_token'] ?? ''));
        $primaryCalendar = collect($calendars)->first(fn (array $item) => (bool) ($item['primary'] ?? false));

        $cuenta = $integracion->cuentas()
            ->where('activo', true)
            ->latest('id')
            ->first() ?? new IntegracionCuenta();

        $emailExterno = $this->resolveAccountEmail($primaryCalendar);

        $cuenta->integracion()->associate($integracion);
        $cuenta->fill([
            'cuenta_externa_id' => $emailExterno,
            'email_externo' => $emailExterno,
            'nombre_externo' => $primaryCalendar['summary'] ?? 'Google Calendar',
            'access_token' => $tokens['access_token'] ?? null,
            'refresh_token' => $tokens['refresh_token'] ?? $cuenta->refresh_token,
            'token_expira_en' => isset($tokens['expires_in']) ? now()->addSeconds((int) $tokens['expires_in']) : null,
            'scopes' => $tokens['scope'] ?? implode(' ', config('services.google_calendar.scopes', [])),
            'datos_extra' => [
                'token_type' => $tokens['token_type'] ?? null,
                'id_token' => $tokens['id_token'] ?? null,
            ],
            'activo' => true,
        ]);
        $cuenta->save();

        $integracion->fill([
            'nombre' => 'Google Calendar',
            'modo_operacion' => $integracion->modo_operacion ?: 'coexistencia',
            'estado' => 'conectada',
            'ultimo_sync_at' => now(),
            'ultimo_error' => null,
        ]);
        $integracion->save();

        return $integracion->fresh(['cuentaActiva']);
    }

    public function syncToggleState(Negocio $negocio, bool $enabled): ?Integracion
    {
        $integracion = $this->findIntegration($negocio);

        if (! $enabled && $integracion === null) {
            return null;
        }

        $integracion ??= $this->ensureIntegration($negocio);
        $integracion->activo = $enabled;
        $integracion->save();

        return $integracion;
    }

    public function findIntegration(Negocio $negocio, bool $requireActive = false): ?Integracion
    {
        $query = $negocio->integraciones()
            ->where('proveedor', 'google_calendar')
            ->latest('id');

        if ($requireActive) {
            $query->where('activo', true);
        }

        return $query->first();
    }

    public function connectedIntegration(Negocio $negocio, bool $requireActive = false): ?Integracion
    {
        $integracion = $this->findIntegration($negocio, $requireActive);

        if (! $integracion || ! $integracion->estaConectada()) {
            return null;
        }

        return $integracion;
    }

    public function isEnabledForBusiness(Negocio $negocio): bool
    {
        $integracion = $this->connectedIntegration($negocio, requireActive: true);

        if (! $integracion) {
            return false;
        }

        return $this->activeAccount($integracion) !== null;
    }

    public function activeAccount(Integracion $integracion): ?IntegracionCuenta
    {
        if ($integracion->relationLoaded('cuentaActiva')) {
            return $integracion->cuentaActiva;
        }

        return $integracion->cuentas()
            ->where('activo', true)
            ->latest('id')
            ->first();
    }

    public function selectedCalendars(Integracion $integracion): Collection
    {
        if ($integracion->relationLoaded('calendariosSeleccionados')) {
            return $integracion->calendariosSeleccionados;
        }

        return $integracion->calendariosSeleccionados()
            ->orderByDesc('es_primario')
            ->orderBy('nombre_externo')
            ->get();
    }

    public function accessToken(Integracion $integracion): string
    {
        $cuenta = $this->activeAccount($integracion);

        if (! $cuenta || ! $cuenta->access_token) {
            throw new RuntimeException('La integración de Google Calendar no tiene una cuenta activa autorizada.');
        }

        if ($cuenta->token_expira_en !== null && $cuenta->token_expira_en->subMinute()->isFuture()) {
            return $cuenta->access_token;
        }

        if (! $cuenta->refresh_token) {
            throw new RuntimeException('La cuenta de Google Calendar no dispone de refresh token para renovar el acceso.');
        }

        $tokens = $this->client->refreshAccessToken($cuenta->refresh_token);
        $cuenta->fill([
            'access_token' => $tokens['access_token'] ?? $cuenta->access_token,
            'refresh_token' => $tokens['refresh_token'] ?? $cuenta->refresh_token,
            'token_expira_en' => isset($tokens['expires_in']) ? now()->addSeconds((int) $tokens['expires_in']) : $cuenta->token_expira_en,
            'scopes' => $tokens['scope'] ?? $cuenta->scopes,
            'datos_extra' => array_filter(array_merge($cuenta->datos_extra ?? [], [
                'token_type' => $tokens['token_type'] ?? null,
            ]), static fn ($value) => $value !== null),
        ]);
        $cuenta->save();

        return (string) $cuenta->access_token;
    }

    public function mergeCalendarSelections(Integracion $integracion, array $calendarPayloads): Collection
    {
        $seen = [];

        foreach ($calendarPayloads as $calendar) {
            $calendarId = (string) ($calendar['google_calendar_id'] ?? '');
            if ($calendarId === '') {
                continue;
            }

            $seen[] = $calendarId;

            IntegracionMapeo::query()->updateOrCreate(
                [
                    'integracion_id' => $integracion->id,
                    'external_id' => $calendarId,
                ],
                [
                    'tipo_origen' => 'calendario',
                    'external_parent_id' => $this->activeAccount($integracion)?->email_externo,
                    'nombre_externo' => $calendar['summary'] ?? null,
                    'timezone' => $calendar['timezone'] ?? null,
                    'es_primario' => (bool) ($calendar['is_primary'] ?? false),
                    'seleccionado' => (bool) ($calendar['selected'] ?? false),
                    'negocio_id' => $integracion->negocio_id,
                    'recurso_id' => $calendar['resource_id'] ?? null,
                    'configuracion' => null,
                    'datos_extra' => [
                        'background_color' => $calendar['background_color'] ?? null,
                        'access_role' => $calendar['access_role'] ?? null,
                    ],
                    'activo' => true,
                ]
            );
        }

        $query = IntegracionMapeo::query()
            ->where('integracion_id', $integracion->id)
            ->where('tipo_origen', 'calendario');

        if ($seen !== []) {
            $query->whereNotIn('external_id', $seen);
        }

        $query->update([
            'seleccionado' => false,
        ]);

        return $this->selectedCalendars($integracion->fresh());
    }

    private function ensureIntegration(Negocio $negocio): Integracion
    {
        return Integracion::query()->firstOrCreate(
            [
                'negocio_id' => $negocio->id,
                'proveedor' => 'google_calendar',
            ],
            [
                'nombre' => 'Google Calendar',
                'modo_operacion' => 'coexistencia',
                'estado' => 'pendiente',
                'activo' => false,
            ]
        );
    }

    private function redirectUri(): string
    {
        return (string) config('services.google_calendar.redirect_uri');
    }

    private function buildState(Negocio $negocio): string
    {
        return Crypt::encryptString(json_encode([
            'business_id' => $negocio->id,
            'issued_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));
    }

    private function decodeState(string $state): array
    {
        try {
            $decoded = json_decode(Crypt::decryptString($state), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            throw new RuntimeException('El estado OAuth de Google Calendar no es válido.', previous: $exception);
        }

        $issuedAt = Carbon::createFromTimestamp((int) ($decoded['issued_at'] ?? 0));

        if ($issuedAt->addMinutes(30)->isPast()) {
            throw new RuntimeException('La autorización de Google Calendar ha caducado. Vuelve a iniciar la conexión.');
        }

        return $decoded;
    }

    private function resolveAccountEmail(?array $primaryCalendar): ?string
    {
        $candidate = $primaryCalendar['id'] ?? null;

        if (! is_string($candidate) || ! str_contains($candidate, '@')) {
            return null;
        }

        return $candidate;
    }
}

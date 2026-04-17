<?php

namespace App\Services\Integrations;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCalendarClient
{
    public function buildAuthorizationUrl(string $state, string $redirectUri): string
    {
        $clientId = (string) config('services.google_calendar.client_id', '');

        if ($clientId === '') {
            throw new RuntimeException('Google Calendar client_id no está configurado.');
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'scope' => implode(' ', config('services.google_calendar.scopes', [])),
            'state' => $state,
        ]);

        return rtrim((string) config('services.google_calendar.auth_base_url'), '?').'?'.$query;
    }

    public function exchangeCodeForTokens(string $code, string $redirectUri): array
    {
        $response = Http::asForm()
            ->timeout(20)
            ->post((string) config('services.google_calendar.token_url'), [
                'code' => $code,
                'client_id' => (string) config('services.google_calendar.client_id'),
                'client_secret' => (string) config('services.google_calendar.client_secret'),
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

        return $this->decodeResponse($response, 'intercambiar el código OAuth con Google');
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()
            ->timeout(20)
            ->post((string) config('services.google_calendar.token_url'), [
                'client_id' => (string) config('services.google_calendar.client_id'),
                'client_secret' => (string) config('services.google_calendar.client_secret'),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        return $this->decodeResponse($response, 'refrescar el access token de Google Calendar');
    }

    public function listCalendars(string $accessToken): array
    {
        $items = [];
        $pageToken = null;

        do {
            $response = Http::withToken($accessToken)
                ->timeout(20)
                ->get($this->apiUrl('/users/me/calendarList'), array_filter([
                    'maxResults' => 250,
                    'pageToken' => $pageToken,
                ], static fn ($value) => $value !== null));

            $payload = $this->decodeResponse($response, 'obtener la lista de calendarios de Google');
            $items = array_merge($items, Arr::get($payload, 'items', []));
            $pageToken = Arr::get($payload, 'nextPageToken');
        } while ($pageToken !== null);

        return $items;
    }

    public function listEvents(
        string $accessToken,
        string $calendarId,
        CarbonInterface $timeMin,
        CarbonInterface $timeMax
    ): array {
        $items = [];
        $pageToken = null;

        do {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get($this->apiUrl('/calendars/'.rawurlencode($calendarId).'/events'), array_filter([
                    'singleEvents' => 'true',
                    'orderBy' => 'startTime',
                    'showDeleted' => 'true',
                    'maxResults' => 2500,
                    'timeMin' => $timeMin->toIso8601String(),
                    'timeMax' => $timeMax->toIso8601String(),
                    'pageToken' => $pageToken,
                ], static fn ($value) => $value !== null));

            $payload = $this->decodeResponse($response, 'obtener eventos de Google Calendar');
            $items = array_merge($items, Arr::get($payload, 'items', []));
            $pageToken = Arr::get($payload, 'nextPageToken');
        } while ($pageToken !== null);

        return $items;
    }

    public function createEvent(string $accessToken, string $calendarId, array $payload): array
    {
        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->post($this->apiUrl('/calendars/'.rawurlencode($calendarId).'/events'), $payload);

        return $this->decodeResponse($response, 'crear un evento en Google Calendar');
    }

    public function updateEvent(string $accessToken, string $calendarId, string $eventId, array $payload): array
    {
        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->put($this->apiUrl('/calendars/'.rawurlencode($calendarId).'/events/'.rawurlencode($eventId)), $payload);

        return $this->decodeResponse($response, 'actualizar un evento en Google Calendar');
    }

    public function deleteEvent(string $accessToken, string $calendarId, string $eventId): void
    {
        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->delete($this->apiUrl('/calendars/'.rawurlencode($calendarId).'/events/'.rawurlencode($eventId)));

        if ($response->status() === 404 || $response->successful()) {
            return;
        }

        $this->decodeResponse($response, 'eliminar un evento en Google Calendar');
    }

    public function freeBusy(
        string $accessToken,
        array $calendarIds,
        CarbonInterface $timeMin,
        CarbonInterface $timeMax,
        ?string $timezone = null
    ): array {
        $items = array_values(array_map(
            static fn (string $id) => ['id' => $id],
            array_values(array_unique(array_filter($calendarIds)))
        ));

        if ($items === []) {
            return ['calendars' => []];
        }

        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->post($this->apiUrl('/freeBusy'), [
                'timeMin' => $timeMin->toIso8601String(),
                'timeMax' => $timeMax->toIso8601String(),
                'timeZone' => $timezone,
                'items' => $items,
            ]);

        return $this->decodeResponse($response, 'consultar freeBusy en Google Calendar');
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('services.google_calendar.api_base_url'), '/').$path;
    }

    private function decodeResponse(Response $response, string $action): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $message = Arr::get($response->json(), 'error.message')
            ?? Arr::get($response->json(), 'error_description')
            ?? $response->body();

        throw new RuntimeException("No se pudo {$action}: ".trim((string) $message));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Onboarding\ProvisionBusinessFromOnboardingSession;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBusinessOnboardingSessionRequest;
use App\Jobs\RunBusinessOnboardingDiscoveryJob;
use App\Models\BusinessOnboardingSession;
use App\Models\TipoNegocio;
use App\Support\OnboardingUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class BusinessOnboardingSessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->abortUnlessFullAdmin($request);

        $sessions = BusinessOnboardingSession::query()
            ->with(['createdBy:id,name,email', 'requestedTipoNegocio:id,nombre', 'provisionedNegocio:id,nombre'])
            ->withCount('sources')
            ->latest('id')
            ->paginate(15);

        return view('admin.configurador-negocios.index', [
            'sessions' => $sessions,
        ]);
    }

    public function create(Request $request): View
    {
        $this->abortUnlessFullAdmin($request);

        $businessTypes = TipoNegocio::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $defaultBusinessType = $businessTypes->first(
            fn (TipoNegocio $type) => str_contains(mb_strtolower($type->nombre), 'bodega')
        )?->id ?? $businessTypes->first()?->id;

        return view('admin.configurador-negocios.create', [
            'businessTypes' => $businessTypes,
            'defaultBusinessType' => $defaultBusinessType,
        ]);
    }

    public function store(StoreBusinessOnboardingSessionRequest $request): RedirectResponse
    {
        $this->abortUnlessFullAdmin($request);

        $validated = $request->validated();
        $password = $validated['requested_admin_password'] ?? null;

        unset($validated['requested_admin_password']);

        $sourceUrl = (string) $validated['source_url'];

        $session = BusinessOnboardingSession::create([
            ...$validated,
            'created_by_user_id' => $request->user()?->id,
            'source_url' => $sourceUrl,
            'source_host' => (string) OnboardingUrl::host($sourceUrl),
            'requested_admin_password_hash' => filled($password) ? Hash::make((string) $password) : null,
        ]);

        try {
            RunBusinessOnboardingDiscoveryJob::dispatchSync($session->id);
        } catch (\Throwable) {
            return redirect()
                ->route('admin.configurador-negocios.show', $session)
                ->with('error', 'La sesion se ha creado, pero la exploracion inicial ha fallado. Revisa la URL o relanzala desde la ficha.');
        }

        return redirect()
            ->route('admin.configurador-negocios.show', $session)
            ->with('success', 'La sesion del configurador se ha creado y el descubrimiento inicial ya esta listo para revisar.');
    }

    public function show(Request $request, BusinessOnboardingSession $businessOnboardingSession): View
    {
        $this->abortUnlessFullAdmin($request);

        $businessOnboardingSession->load([
            'createdBy:id,name,email',
            'requestedTipoNegocio:id,nombre',
            'provisionedNegocio:id,nombre',
            'sources' => fn ($query) => $query->latest('id'),
        ]);

        return view('admin.configurador-negocios.show', [
            'session' => $businessOnboardingSession,
            'draft' => $businessOnboardingSession->draft(),
        ]);
    }

    public function rediscover(Request $request, BusinessOnboardingSession $businessOnboardingSession): RedirectResponse
    {
        $this->abortUnlessFullAdmin($request);

        try {
            RunBusinessOnboardingDiscoveryJob::dispatchSync($businessOnboardingSession->id);
        } catch (\Throwable) {
            return redirect()
                ->route('admin.configurador-negocios.show', $businessOnboardingSession)
                ->with('error', 'No se ha podido completar la nueva exploracion. Revisa la URL o prueba mas tarde.');
        }

        return redirect()
            ->route('admin.configurador-negocios.show', $businessOnboardingSession)
            ->with('success', 'Se ha relanzado el descubrimiento sobre la URL del negocio.');
    }

    public function provision(
        Request $request,
        BusinessOnboardingSession $businessOnboardingSession,
        ProvisionBusinessFromOnboardingSession $provisionAction
    ): RedirectResponse {
        $this->abortUnlessFullAdmin($request);

        try {
            $negocio = $provisionAction->handle($businessOnboardingSession);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.configurador-negocios.show', $businessOnboardingSession)
                ->withErrors($exception->errors(), 'provision')
                ->with('error', 'Todavia faltan datos obligatorios para crear el negocio.');
        }

        return redirect()
            ->route('admin.negocios.edit', $negocio)
            ->with('success', 'El negocio se ha creado correctamente desde el configurador.');
    }

    private function abortUnlessFullAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->hasFullAdminAccess(),
            Response::HTTP_FORBIDDEN,
            'Solo un administrador global puede usar el configurador de negocio.'
        );
    }
}

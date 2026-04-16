<?php

namespace App\Livewire\Admin;

use App\Models\Bloqueo;
use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\User;
use App\Support\AdminAccess;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public string $dashboardType = 'platform';

    public array $stats = [];
    public array $quickLinks = [];
    public array $widgetLinks = [];
    public array $operationalSummary = [];
    public array $chatEntry = [];
    public array $businessProfile = [];
    public array $businessUpcomingReservations = [];

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user && ! $user->hasFullAdminAccess()) {
            $this->mountBusinessDashboard($user);

            return;
        }

        $this->mountPlatformDashboard($user);
    }

    public function render()
    {
        $isBusinessDashboard = $this->dashboardType === 'business';

        return view($isBusinessDashboard ? 'livewire.admin.dashboard-business' : 'livewire.admin.dashboard')
            ->layout('layouts.app', [
                'header' => view('livewire.admin.partials.dashboard-header', [
                    'title' => $isBusinessDashboard ? 'Mi negocio' : 'Dashboard',
                    'description' => $isBusinessDashboard
                        ? 'Resumen operativo del negocio, accesos clave y próximas reservas.'
                        : 'Resumen operativo del backoffice, accesos rápidos y entrada directa al chat.',
                ]),
            ]);
    }

    private function mountPlatformDashboard(?User $user): void
    {
        $today = Carbon::today();
        $now = now();

        $negociosActivos = Negocio::activos()->count();
        $negociosTotal = Negocio::count();
        $serviciosActivos = Servicio::activos()->count();
        $serviciosTotal = Servicio::count();
        $clientesTotal = Cliente::count();
        $clientesConReservas = Reserva::query()->distinct('cliente_id')->count('cliente_id');
        $reservasHoy = Reserva::query()->whereDate('fecha', $today)->count();
        $reservasProximas = Reserva::query()
            ->where(function ($query) use ($now, $today) {
                $query->where('inicio_datetime', '>=', $now)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('inicio_datetime')
                            ->whereDate('fecha', '>=', $today);
                    });
            })
            ->count();
        $pagosPendientes = Pago::query()
            ->whereHas('estadoPago', fn ($query) => $query->where('nombre', 'Pendiente'));
        $pagosPendientesCount = (clone $pagosPendientes)->count();
        $pagosPendientesImporte = (float) (clone $pagosPendientes)->sum('importe');
        $recursosActivos = Recurso::activos()->count();
        $disponibilidadesActivas = Disponibilidad::activos()->count();
        $bloqueosProximos = Bloqueo::query()
            ->where('activo', true)
            ->where(function ($query) use ($today) {
                $query->whereDate('fecha', '>=', $today)
                    ->orWhereDate('fecha_inicio', '>=', $today)
                    ->orWhere(function ($range) use ($today) {
                        $range->whereNotNull('fecha_fin')
                            ->whereDate('fecha_fin', '>=', $today);
                    });
            })
            ->count();

        $this->stats = [
            [
                'label' => 'Negocios activos',
                'value' => $negociosActivos,
                'meta' => $this->buildCountMeta($negociosTotal, 'registrado', 'registrados'),
                'icon' => 'fas fa-store',
                'href' => route('admin.negocios.index'),
            ],
            [
                'label' => 'Servicios activos',
                'value' => $serviciosActivos,
                'meta' => $this->buildCountMeta($serviciosTotal, 'servicio total', 'servicios totales'),
                'icon' => 'fas fa-concierge-bell',
                'href' => route('admin.servicios.index'),
            ],
            [
                'label' => 'Reservas próximas',
                'value' => $reservasProximas,
                'meta' => $this->buildCountMeta($reservasHoy, 'reserva hoy', 'reservas hoy'),
                'icon' => 'fas fa-calendar-check',
                'href' => route('admin.reservas.index'),
            ],
            [
                'label' => 'Pagos pendientes',
                'value' => $pagosPendientesCount,
                'meta' => $this->formatEuros($pagosPendientesImporte).' por revisar',
                'icon' => 'fas fa-credit-card',
                'href' => route('admin.pagos.index'),
            ],
        ];

        $this->chatEntry = [
            'title' => 'Chat conversacional',
            'description' => 'Accede directamente a la prueba del asistente con contexto de negocio, herramientas MCP y flujo LLM.',
            'href' => route('admin.chat-test.index'),
            'button' => 'Abrir chat',
            'meta' => $this->buildCountMeta($negociosActivos, 'negocio disponible', 'negocios disponibles').' para pruebas',
        ];

        $this->quickLinks = [
            [
                'title' => 'Negocios',
                'description' => 'Configuración general, tono conversacional y políticas del negocio.',
                'icon' => 'fas fa-store',
                'href' => route('admin.negocios.index'),
                'badge' => (string) $negociosTotal,
            ],
            [
                'title' => 'Clientes',
                'description' => 'Ficha de clientes y relación con reservas existentes.',
                'icon' => 'fas fa-users',
                'href' => route('admin.clientes.index'),
                'badge' => (string) $clientesTotal,
            ],
            [
                'title' => 'Servicios',
                'description' => 'Oferta reservable, precios base y sincronización con recursos.',
                'icon' => 'fas fa-concierge-bell',
                'href' => route('admin.servicios.index'),
                'badge' => (string) $serviciosActivos,
            ],
            [
                'title' => 'Recursos',
                'description' => 'Recursos operativos, capacidades y combinaciones disponibles.',
                'icon' => 'fas fa-layer-group',
                'href' => route('admin.recursos.index'),
                'badge' => (string) $recursosActivos,
            ],
            [
                'title' => 'Disponibilidades',
                'description' => 'Configuración semanal de horarios base por recurso.',
                'icon' => 'fas fa-clock',
                'href' => route('admin.disponibilidades.index'),
                'badge' => (string) $disponibilidadesActivas,
            ],
            [
                'title' => 'Bloqueos',
                'description' => 'Cierres puntuales, mantenimientos y eventos especiales.',
                'icon' => 'fas fa-ban',
                'href' => route('admin.bloqueos.index'),
                'badge' => (string) $bloqueosProximos,
            ],
            [
                'title' => 'Reservas',
                'description' => 'Operativa diaria, estados y seguimiento de próximas visitas.',
                'icon' => 'fas fa-calendar-alt',
                'href' => route('admin.reservas.index'),
                'badge' => (string) $reservasProximas,
            ],
            [
                'title' => 'Pagos',
                'description' => 'Cobros, referencias externas y estados de pago.',
                'icon' => 'fas fa-wallet',
                'href' => route('admin.pagos.index'),
                'badge' => (string) $pagosPendientesCount,
            ],
        ];

        $this->operationalSummary = [
            [
                'label' => 'Clientes con historial',
                'value' => $clientesConReservas,
                'href' => route('admin.clientes.index'),
            ],
            [
                'label' => 'Reservas para hoy',
                'value' => $reservasHoy,
                'href' => route('admin.reservas.index'),
            ],
            [
                'label' => 'Bloqueos próximos',
                'value' => $bloqueosProximos,
                'href' => route('admin.bloqueos.index'),
            ],
            [
                'label' => 'Importe pendiente',
                'value' => $this->formatEuros($pagosPendientesImporte),
                'href' => route('admin.pagos.index'),
            ],
        ];

        $widgetBusinesses = $user?->negocios()
            ->with('tipoNegocio:id,nombre')
            ->orderBy('nombre')
            ->get() ?? collect();

        if ($widgetBusinesses->isEmpty()) {
            $widgetBusinesses = Negocio::query()
                ->with('tipoNegocio:id,nombre')
                ->orderBy('nombre')
                ->get();
        }

        $this->widgetLinks = $widgetBusinesses
            ->map(function (Negocio $negocio): array {
                return [
                    'title' => $negocio->nombre,
                    'subtitle' => $negocio->tipoNegocio?->nombre ?? 'Negocio',
                    'status' => $negocio->widget_enabled ? 'Activo' : 'Inactivo',
                    'detail' => $negocio->widget_enabled
                        ? 'Widget listo para publicar'
                        : 'Pendiente de activar',
                    'enabled' => (bool) $negocio->widget_enabled,
                    'href' => route('admin.negocios.edit', $negocio).'#widget-calendar-settings',
                ];
            })
            ->all();
    }

    private function mountBusinessDashboard(User $user): void
    {
        $this->dashboardType = 'business';

        $business = app(AdminAccess::class)
            ->accessibleBusinessesQuery($user)
            ->with('tipoNegocio')
            ->orderBy('nombre')
            ->first();

        if (! $business) {
            $this->operationalSummary = [
                [
                    'label' => 'Asignación',
                    'value' => 'Pendiente',
                    'href' => route('dashboard'),
                ],
            ];

            return;
        }

        $today = Carbon::today();
        $now = now();

        $reservasBase = Reserva::query()->where('negocio_id', $business->id);
        $serviciosBase = Servicio::query()->where('negocio_id', $business->id);
        $recursosBase = Recurso::query()->where('negocio_id', $business->id);
        $pagosPendientes = Pago::query()
            ->whereHas('reserva', fn ($query) => $query->where('negocio_id', $business->id))
            ->whereHas('estadoPago', fn ($query) => $query->where('nombre', 'Pendiente'));

        $reservasHoy = (clone $reservasBase)->whereDate('fecha', $today)->count();
        $reservasProximas = (clone $reservasBase)
            ->where(function ($query) use ($now, $today) {
                $query->where('inicio_datetime', '>=', $now)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('inicio_datetime')
                            ->whereDate('fecha', '>=', $today);
                    });
            })
            ->count();
        $serviciosActivos = (clone $serviciosBase)->activos()->count();
        $serviciosTotal = (clone $serviciosBase)->count();
        $recursosActivos = (clone $recursosBase)->activos()->count();
        $recursosTotal = (clone $recursosBase)->count();
        $pagosPendientesCount = (clone $pagosPendientes)->count();
        $pagosPendientesImporte = (float) (clone $pagosPendientes)->sum('importe');

        $integration = $business->integracionGoogleCalendar()->with('cuentaActiva')->first();
        $googleStatus = match (true) {
            ! $integration => 'Sin configurar',
            $integration->activo && $integration->estaConectada() => 'Conectado',
            $integration->activo => 'Pendiente',
            default => 'Desactivado',
        };

        $this->stats = [
            [
                'label' => 'Reservas hoy',
                'value' => $reservasHoy,
                'meta' => $business->nombre,
                'icon' => 'fas fa-calendar-day',
                'href' => route('admin.reservas.index'),
            ],
            [
                'label' => 'Reservas próximas',
                'value' => $reservasProximas,
                'meta' => 'Agenda futura del negocio',
                'icon' => 'fas fa-calendar-check',
                'href' => route('admin.calendario.index'),
            ],
            [
                'label' => 'Servicios activos',
                'value' => $serviciosActivos,
                'meta' => $this->buildCountMeta($serviciosTotal, 'servicio total', 'servicios totales'),
                'icon' => 'fas fa-concierge-bell',
                'href' => route('admin.servicios.index'),
            ],
            [
                'label' => 'Recursos activos',
                'value' => $recursosActivos,
                'meta' => $this->buildCountMeta($recursosTotal, 'recurso total', 'recursos totales'),
                'icon' => 'fas fa-layer-group',
                'href' => route('admin.recursos.index'),
            ],
        ];

        $this->businessProfile = [
            'name' => $business->nombre,
            'type' => $business->tipoNegocio?->nombre ?? 'Negocio',
            'email' => $business->email ?: 'Sin email',
            'phone' => $business->telefono ?: 'Sin teléfono',
            'timezone' => $business->zona_horaria,
            'status' => $business->activo ? 'Activo' : 'Inactivo',
            'edit_href' => route('admin.negocios.edit', $business),
        ];

        $this->operationalSummary = [
            [
                'label' => 'Google Calendar',
                'value' => $googleStatus,
                'href' => route('admin.negocios.edit', $business).'#google-calendar-settings',
            ],
            [
                'label' => 'Widget calendario',
                'value' => $business->widget_enabled ? 'Activo' : 'Inactivo',
                'href' => route('admin.negocios.edit', $business).'#widget-calendar-settings',
            ],
            [
                'label' => 'Pagos pendientes',
                'value' => $pagosPendientesCount.' · '.$this->formatEuros($pagosPendientesImporte),
                'href' => route('admin.pagos.index'),
            ],
            [
                'label' => 'Administrador',
                'value' => $user->name,
                'href' => route('admin.negocios.edit', $business),
            ],
        ];

        $this->quickLinks = [
            [
                'title' => 'Configurar negocio',
                'description' => 'Datos generales, tono conversacional y reglas del negocio.',
                'icon' => 'fas fa-store',
                'href' => route('admin.negocios.edit', $business),
                'badge' => $business->activo ? 'Activo' : 'Inactivo',
            ],
            [
                'title' => 'Google Calendar',
                'description' => 'Cuenta conectada, calendarios y sincronización inicial.',
                'icon' => 'fas fa-calendar-plus',
                'href' => route('admin.negocios.edit', $business).'#google-calendar-settings',
                'badge' => $googleStatus,
            ],
            [
                'title' => 'Widget calendario',
                'description' => 'Ajustes del widget público y clave de integración.',
                'icon' => 'fas fa-window-maximize',
                'href' => route('admin.negocios.edit', $business).'#widget-calendar-settings',
                'badge' => $business->widget_enabled ? 'Activo' : 'Inactivo',
            ],
            [
                'title' => 'Reservas',
                'description' => 'Seguimiento diario, estados y gestión operativa.',
                'icon' => 'fas fa-calendar-alt',
                'href' => route('admin.reservas.index'),
                'badge' => (string) $reservasProximas,
            ],
            [
                'title' => 'Calendario',
                'description' => 'Visión mensual del negocio y detalle diario.',
                'icon' => 'fas fa-calendar-week',
                'href' => route('admin.calendario.index'),
                'badge' => 'Agenda',
            ],
            [
                'title' => 'Servicios',
                'description' => 'Oferta reservable, duraciones y precios base.',
                'icon' => 'fas fa-concierge-bell',
                'href' => route('admin.servicios.index'),
                'badge' => (string) $serviciosActivos,
            ],
            [
                'title' => 'Recursos',
                'description' => 'Capacidades, combinaciones y recursos operativos.',
                'icon' => 'fas fa-layer-group',
                'href' => route('admin.recursos.index'),
                'badge' => (string) $recursosActivos,
            ],
            [
                'title' => 'Disponibilidades',
                'description' => 'Horarios base y aperturas por recurso.',
                'icon' => 'fas fa-clock',
                'href' => route('admin.disponibilidades.index'),
                'badge' => 'Horario',
            ],
            [
                'title' => 'Bloqueos',
                'description' => 'Cierres puntuales, incidencias y eventos especiales.',
                'icon' => 'fas fa-ban',
                'href' => route('admin.bloqueos.index'),
                'badge' => 'Agenda',
            ],
            [
                'title' => 'Clientes',
                'description' => 'Ficha de clientes y su historial de reservas.',
                'icon' => 'fas fa-users',
                'href' => route('admin.clientes.index'),
                'badge' => 'CRM',
            ],
            [
                'title' => 'Pagos',
                'description' => 'Cobros, referencias y estados pendientes.',
                'icon' => 'fas fa-wallet',
                'href' => route('admin.pagos.index'),
                'badge' => (string) $pagosPendientesCount,
            ],
        ];

        $this->businessUpcomingReservations = Reserva::query()
            ->where('negocio_id', $business->id)
            ->whereHas('estadoReserva', fn ($query) => $query->whereNotIn('nombre', ['Cancelada']))
            ->where(function ($query) use ($now, $today) {
                $query->where('inicio_datetime', '>=', $now)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('inicio_datetime')
                            ->whereDate('fecha', '>=', $today);
                    });
            })
            ->with(['cliente:id,nombre', 'servicio:id,nombre', 'estadoReserva:id,nombre'])
            ->orderBy('inicio_datetime')
            ->orderBy('fecha')
            ->limit(6)
            ->get()
            ->map(function (Reserva $reserva): array {
                return [
                    'id' => $reserva->id,
                    'date' => $reserva->fecha?->format('d/m/Y'),
                    'time' => substr((string) $reserva->hora_inicio, 0, 5).' - '.substr((string) $reserva->hora_fin, 0, 5),
                    'client' => $reserva->cliente?->nombre ?? 'Sin cliente',
                    'service' => $reserva->servicio?->nombre ?? 'Sin servicio',
                    'status' => $reserva->estadoReserva?->nombre ?? 'Sin estado',
                    'href' => route('admin.reservas.show', $reserva),
                ];
            })
            ->all();
    }

    private function buildCountMeta(int $value, string $singular, string $plural): string
    {
        return $value.' '.($value === 1 ? $singular : $plural);
    }

    private function formatEuros(float $amount): string
    {
        return number_format($amount, 2, ',', '.').' EUR';
    }
}

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
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];
    public array $quickLinks = [];
    public array $widgetLinks = [];
    public array $operationalSummary = [];
    public array $chatEntry = [];

    public function mount(): void
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
                'href' => route('admin.reservas.index', ['date_from' => $today->toDateString()]),
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

        $userBusinesses = auth()->user()?->negocios();

        $widgetBusinesses = $userBusinesses
            ? $userBusinesses
                ->with('tipoNegocio:id,nombre')
                ->orderBy('nombre')
                ->get()
            : collect();

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

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app', [
                'header' => view('livewire.admin.partials.dashboard-header'),
            ]);
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

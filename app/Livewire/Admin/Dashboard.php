<?php

namespace App\Livewire\Admin;

use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\Servicio;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];
    public array $quickLinks = [];

    public function mount(): void
    {
        $this->stats = [
            [
                'label' => 'NEGOCIOS',
                'value' => Negocio::count(),
                'icon' => 'fas fa-store',
            ],
            [
                'label' => 'SERVICIOS',
                'value' => Servicio::count(),
                'icon' => 'fas fa-concierge-bell',
            ],
            [
                'label' => 'CLIENTES',
                'value' => Cliente::count(),
                'icon' => 'fas fa-users',
            ],
            [
                'label' => 'RESERVAS',
                'value' => Reserva::count(),
                'icon' => 'fas fa-calendar-check',
            ],
        ];

        $this->quickLinks = [
            [
                'title' => 'Negocios',
                'description' => 'Configuracion de negocios y sedes',
                'icon' => 'fas fa-store',
            ],
            [
                'title' => 'Servicios',
                'description' => 'Catalogo y tipologias de servicio',
                'icon' => 'fas fa-concierge-bell',
            ],
            [
                'title' => 'Recursos',
                'description' => 'Recursos, disponibilidades y bloqueos',
                'icon' => 'fas fa-layer-group',
            ],
            [
                'title' => 'Reservas',
                'description' => 'Gestion operativa de las reservas',
                'icon' => 'fas fa-calendar-check',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app', [
                'header' => view('livewire.admin.partials.dashboard-header'),
            ]);
    }
}

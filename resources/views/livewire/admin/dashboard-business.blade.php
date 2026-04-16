<div class="clockia-dashboard">
    <div class="row clockia-dashboard__stats">
        @foreach ($stats as $stat)
            <div class="col-lg-3 col-sm-6">
                <a href="{{ $stat['href'] }}" class="clockia-stat clockia-stat--link">
                    <div class="clockia-stat__icon">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                    <div class="clockia-stat__label">{{ $stat['label'] }}</div>
                    <p class="clockia-stat__value">{{ $stat['value'] }}</p>
                    <div class="clockia-stat__meta">{{ $stat['meta'] }}</div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row clockia-dashboard__panels">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header clockia-card-header-inline">
                    <h3 class="card-title">
                        <i class="fas fa-store mr-2 text-primary"></i>
                        {{ $businessProfile['name'] ?? 'Mi negocio' }}
                    </h3>
                    @if (! empty($businessProfile['edit_href']))
                        <a href="{{ $businessProfile['edit_href'] }}" class="btn btn-sm btn-outline-primary">
                            Configurar
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if ($businessProfile !== [])
                        <div class="row">
                            <div class="col-md-6">
                                <div class="clockia-meta-row">
                                    <span class="clockia-meta-row__label">Tipo</span>
                                    <span class="clockia-meta-row__value">{{ $businessProfile['type'] }}</span>
                                </div>
                                <div class="clockia-meta-row">
                                    <span class="clockia-meta-row__label">Email</span>
                                    <span class="clockia-meta-row__value">{{ $businessProfile['email'] }}</span>
                                </div>
                                <div class="clockia-meta-row">
                                    <span class="clockia-meta-row__label">Teléfono</span>
                                    <span class="clockia-meta-row__value">{{ $businessProfile['phone'] }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="clockia-meta-row">
                                    <span class="clockia-meta-row__label">Zona horaria</span>
                                    <span class="clockia-meta-row__value">{{ $businessProfile['timezone'] }}</span>
                                </div>
                                <div class="clockia-meta-row">
                                    <span class="clockia-meta-row__label">Estado</span>
                                    <span class="clockia-meta-row__value">{{ $businessProfile['status'] }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="clockia-empty-state">
                            Tu usuario todavía no tiene un negocio asignado.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2 text-primary"></i>
                        Estado rápido
                    </h3>
                </div>
                <div class="card-body">
                    @foreach ($operationalSummary as $item)
                        <a href="{{ $item['href'] }}" class="clockia-meta-row clockia-meta-row--link">
                            <span class="clockia-meta-row__label">{{ $item['label'] }}</span>
                            <span class="clockia-meta-row__value">{{ $item['value'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row clockia-dashboard__panels">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                        Próximas reservas
                    </h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($businessUpcomingReservations as $reservation)
                        <a href="{{ $reservation['href'] }}" class="list-group-item clockia-widget-link">
                            <span class="clockia-widget-link__content">
                                <span class="clockia-widget-link__title">{{ $reservation['client'] }}</span>
                                <span class="clockia-widget-link__subtitle">
                                    {{ $reservation['date'] }} · {{ $reservation['time'] }} · {{ $reservation['service'] }}
                                </span>
                            </span>
                            <span class="clockia-widget-link__meta">
                                <span class="clockia-widget-link__status is-active">
                                    {{ $reservation['status'] }}
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="clockia-empty-state">
                            No hay reservas próximas pendientes de revisar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-compass mr-2 text-primary"></i>
                        Accesos rápidos
                    </h3>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['href'] }}" class="list-group-item clockia-quick-link">
                            <span class="clockia-quick-link__icon">
                                <i class="{{ $link['icon'] }}"></i>
                            </span>
                            <span class="clockia-quick-link__content">
                                <span class="clockia-quick-link__title">{{ $link['title'] }}</span>
                                <span class="clockia-quick-link__description">{{ $link['description'] }}</span>
                            </span>
                            <span class="clockia-quick-link__badge">{{ $link['badge'] }}</span>
                            <span class="clockia-quick-link__arrow">
                                <i class="fas fa-angle-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

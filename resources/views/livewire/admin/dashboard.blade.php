<div class="clockia-dashboard">
    <div class="row clockia-dashboard__stats">
        @foreach ($stats as $stat)
            <div class="col-lg-3 col-6">
                <div class="clockia-stat">
                    <div class="clockia-stat__icon">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                    <div class="clockia-stat__label">{{ $stat['label'] }}</div>
                    <p class="clockia-stat__value">{{ $stat['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row clockia-dashboard__panels">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2 text-primary"></i>
                        Acceso rapido
                    </h3>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($quickLinks as $link)
                        <a href="{{ route('dashboard') }}" class="list-group-item clockia-quick-link">
                            <span class="clockia-quick-link__icon">
                                <i class="{{ $link['icon'] }}"></i>
                            </span>
                            <span class="clockia-quick-link__content">
                                <span class="clockia-quick-link__title">{{ $link['title'] }}</span>
                                <span class="clockia-quick-link__description">{{ $link['description'] }}</span>
                            </span>
                            <span class="clockia-quick-link__arrow">
                                <i class="fas fa-angle-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        Tu cuenta
                    </h3>
                </div>
                <div class="card-body">
                    <div class="clockia-account">
                        <div class="clockia-account__avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="clockia-account__name">{{ auth()->user()->name }}</div>
                        <div class="clockia-account__email">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="clockia-account__meta">
                        <div class="clockia-meta-row">
                            <span class="clockia-meta-row__label">Perfil</span>
                            <span class="clockia-meta-row__value">Administrador</span>
                        </div>
                        <div class="clockia-meta-row">
                            <span class="clockia-meta-row__label">Accesos</span>
                            <span class="clockia-meta-row__value">Panel interno</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

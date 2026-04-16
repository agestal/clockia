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
        <div class="col-lg-8">
            <div class="card clockia-chat-entry">
                <div class="card-body">
                    <div class="clockia-chat-entry__content">
                        <div class="clockia-chat-entry__eyebrow">Prueba conversacional</div>
                        <h2 class="clockia-chat-entry__title">{{ $chatEntry['title'] }}</h2>
                        <p class="clockia-chat-entry__description">{{ $chatEntry['description'] }}</p>
                        <div class="clockia-chat-entry__meta">{{ $chatEntry['meta'] }}</div>
                    </div>
                    <div class="clockia-chat-entry__actions">
                        <a href="{{ $chatEntry['href'] }}" class="btn btn-primary">
                            <i class="fas fa-comments mr-2"></i>
                            {{ $chatEntry['button'] }}
                        </a>
                    </div>
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
    </div>

    <div class="row clockia-dashboard__panels">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header clockia-card-header-inline">
                    <h3 class="card-title">
                        <i class="fas fa-window-maximize mr-2 text-primary"></i>
                        Widgets del calendario
                    </h3>
                    <a href="{{ route('admin.negocios.shortcuts.widget') }}" class="btn btn-sm btn-outline-primary">
                        Configurar widgets
                    </a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($widgetLinks as $widget)
                        <a href="{{ $widget['href'] }}" class="list-group-item clockia-widget-link">
                            <span class="clockia-widget-link__content">
                                <span class="clockia-widget-link__title">{{ $widget['title'] }}</span>
                                <span class="clockia-widget-link__subtitle">{{ $widget['subtitle'] }}</span>
                            </span>
                            <span class="clockia-widget-link__meta">
                                <span class="clockia-widget-link__status @if ($widget['enabled']) is-active @endif">
                                    {{ $widget['status'] }}
                                </span>
                                <span class="clockia-widget-link__detail">{{ $widget['detail'] }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="clockia-empty-state">
                            No hay negocios disponibles para configurar el widget.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2 text-primary"></i>
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

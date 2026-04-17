@extends('layouts.app')

@section('title', 'Configurador de negocio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Configurador de negocio</h1>
            <p class="text-muted mb-0">Lanza una exploracion guiada de la web, revisa el borrador y crea el negocio sin pasar por un alta manual completa.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.configurador-negocios.create') }}" class="btn btn-primary">
                <i class="fas fa-wand-magic-sparkles mr-1"></i>
                Nueva sesion
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Negocio propuesto</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Fuentes</th>
                            <th>Creado por</th>
                            <th>Alta final</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            @php($draft = $session->draft())
                            <tr>
                                <td class="align-middle">
                                    <a href="{{ $session->source_url }}" target="_blank" rel="noopener" class="font-weight-semibold text-decoration-none">
                                        {{ $session->source_host }}
                                    </a>
                                    <div class="small text-muted">{{ $session->source_url }}</div>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-semibold">{{ $draft['business']['nombre'] ?? 'Sin nombre detectado' }}</div>
                                    @if(($draft['admin']['email'] ?? null) || ($draft['admin']['name'] ?? null))
                                        <div class="small text-muted">
                                            {{ $draft['admin']['name'] ?? 'Admin pendiente' }}
                                            @if($draft['admin']['email'] ?? null)
                                                · {{ $draft['admin']['email'] }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $session->requestedTipoNegocio?->nombre ?: 'Sin tipo' }}</td>
                                <td class="align-middle">
                                    <span class="badge {{ $session->statusBadgeClass() }}">{{ $session->statusLabel() }}</span>
                                    @if($session->missingRequiredFieldsResolved() !== [])
                                        <div class="small text-muted mt-1">{{ count($session->missingRequiredFieldsResolved()) }} campos pendientes</div>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $session->sources_count }}</td>
                                <td class="align-middle">
                                    {{ $session->createdBy?->name ?: 'Sistema' }}
                                    <div class="small text-muted">{{ optional($session->created_at)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="align-middle">
                                    @if($session->provisionedNegocio)
                                        <a href="{{ route('admin.negocios.edit', $session->provisionedNegocio) }}" class="font-weight-semibold text-decoration-none">
                                            {{ $session->provisionedNegocio->nombre }}
                                        </a>
                                        <div class="small text-muted">{{ optional($session->provisioned_at)->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="text-muted">Todavia no creado</span>
                                    @endif
                                </td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.configurador-negocios.show', $session) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Aun no hay sesiones del configurador.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($sessions->hasPages())
            <div class="card-footer bg-white">
                {{ $sessions->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

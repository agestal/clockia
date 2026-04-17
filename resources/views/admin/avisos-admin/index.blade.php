@extends('layouts.app')

@section('title', 'Avisos al administrador')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Avisos al administrador</h1>
        <p class="text-muted mb-0">Decide qué notificaciones recibe cada negocio y a qué dirección se envían.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.avisos-admin.index') }}" class="form-row align-items-end">
                <div class="form-group col-lg-4 mb-lg-0">
                    <label for="negocio_id" class="form-label">Negocio</label>
                    <select id="negocio_id" name="negocio_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($negocios as $negocio)
                            <option value="{{ $negocio->id }}" @selected($selectedBusinessId === $negocio->id)>{{ $negocio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-lg-0">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Destino</th>
                            <th>Avisos activos</th>
                            <th>Ultima actualizacion</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($businesses as $business)
                            @php
                                $activeFields = collect($notificationLabels)
                                    ->filter(fn ($label, $field) => (bool) $business->{$field})
                                    ->all();
                            @endphp
                            <tr>
                                <td class="align-middle font-weight-semibold">{{ $business->nombre }}</td>
                                <td class="align-middle text-muted">
                                    {{ $business->notif_email_destino ?: 'Destino automatico' }}
                                </td>
                                <td class="align-middle">
                                    @if($activeFields !== [])
                                        <div class="d-flex flex-wrap" style="gap:.35rem;">
                                            @foreach($activeFields as $label)
                                                <span class="badge badge-light border text-muted">{{ $label }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">Sin avisos activos</span>
                                    @endif
                                </td>
                                <td class="align-middle text-muted">{{ optional($business->updated_at)->format('d/m/Y H:i') }}</td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.avisos-admin.edit', $business) }}" class="btn btn-sm btn-outline-primary">Configurar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay negocios disponibles para los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

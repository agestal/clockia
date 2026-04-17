@extends('layouts.app')

@section('title', 'Bloqueos')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Bloqueos</h1>
            <p class="text-muted mb-0">Gestiona bloqueos de negocio, experiencia o recurso.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.bloqueos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo bloqueo
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.bloqueos.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por recurso, tipo, fecha o motivo">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="fecha" @selected($sort === 'fecha')>Fecha</option>
                            <option value="hora_inicio" @selected($sort === 'hora_inicio')>Hora inicio</option>
                            <option value="hora_fin" @selected($sort === 'hora_fin')>Hora fin</option>
                            <option value="created_at" @selected($sort === 'created_at')>Alta</option>
                        </select>
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="direction">Dirección</label>
                        <select id="direction" name="direction" class="form-control">
                            <option value="desc" @selected($direction === 'desc')>Descendente</option>
                            <option value="asc" @selected($direction === 'asc')>Ascendente</option>
                        </select>
                    </div>

                    <div class="form-group col-md-12 col-lg-3 mb-0 d-flex justify-content-lg-end mt-3 mt-lg-0">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('admin.bloqueos.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ámbito</th>
                        <th>Tipo de bloqueo</th>
                        <th>Modo</th>
                        <th>Fecha / rango</th>
                        <th>Horario</th>
                        <th class="text-center">Activo</th>
                        <th>Motivo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bloqueos as $bloqueo)
                        <tr>
                            <td class="align-top font-weight-semibold">
                                @if($bloqueo->recurso)
                                    <div>{{ $bloqueo->recurso->nombre }}</div>
                                    @if($bloqueo->negocio)
                                        <div class="small text-muted">{{ $bloqueo->negocio->nombre }}</div>
                                    @endif
                                    @if($bloqueo->servicio)
                                        <div class="small text-muted">{{ $bloqueo->servicio->nombre }}</div>
                                    @endif
                                @elseif($bloqueo->negocio)
                                    <div>{{ $bloqueo->negocio->nombre }}</div>
                                    <div class="small text-muted">
                                        {{ $bloqueo->servicio ? $bloqueo->servicio->nombre : 'Negocio completo' }}
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="align-top">{{ $bloqueo->tipoBloqueo?->nombre ?: '—' }}</td>
                            <td class="align-top">
                                @if($bloqueo->es_recurrente)
                                    <span class="badge badge-info">Recurrente</span>
                                @elseif($bloqueo->esRango())
                                    <span class="badge badge-warning">Rango</span>
                                @else
                                    <span class="badge badge-secondary">Puntual</span>
                                @endif
                            </td>
                            <td class="align-top text-muted">
                                @if($bloqueo->es_recurrente)
                                    {{ [0 => 'Dom', 1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb'][$bloqueo->dia_semana] ?? '—' }}
                                @elseif($bloqueo->esRango())
                                    {{ optional($bloqueo->fecha_inicio)->format('d/m/Y') }} → {{ optional($bloqueo->fecha_fin)->format('d/m/Y') }}
                                @else
                                    {{ optional($bloqueo->fecha)->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="align-top text-muted">
                                @if($bloqueo->esDiaCompleto())
                                    <span class="badge badge-light border">Día completo</span>
                                @else
                                    {{ substr((string) $bloqueo->hora_inicio, 0, 5) }} - {{ substr((string) $bloqueo->hora_fin, 0, 5) }}
                                @endif
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $bloqueo->activo ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $bloqueo->activo ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="align-top text-muted">{{ $bloqueo->motivo ?: 'Sin motivo' }}</td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.bloqueos.show', $bloqueo) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.bloqueos.edit', $bloqueo) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.bloqueos.destroy', $bloqueo) }}"
                                    data-message="Vas a eliminar este bloqueo."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No hay bloqueos registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bloqueos->hasPages())
            <div class="card-footer bg-white">
                {{ $bloqueos->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    @include('admin.partials.delete-modal')
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = document.getElementById('delete-confirmation-modal');
            const deleteForm = deleteModal.querySelector('.js-delete-modal-form');
            const deleteMessage = deleteModal.querySelector('.js-delete-modal-message');

            document.querySelectorAll('.js-delete-button').forEach((button) => {
                button.addEventListener('click', () => {
                    deleteForm.action = button.dataset.action;
                    deleteMessage.innerHTML = `${button.dataset.message} Esta acción no se puede deshacer.`;
                    window.jQuery(deleteModal).modal('show');
                });
            });
        });
    </script>
@endpush

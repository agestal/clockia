@extends('layouts.app')

@section('title', 'Ocupaciones externas')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Ocupaciones externas</h1>
            <p class="text-muted mb-0">Gestiona las ocupaciones importadas desde calendarios externos.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.ocupaciones-externas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nueva ocupación externa
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.ocupaciones-externas.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por título, external ID, proveedor o negocio">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="inicio_datetime" @selected($sort === 'inicio_datetime')>Inicio datetime</option>
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
                        <a href="{{ route('admin.ocupaciones-externas.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Negocio</th>
                        <th>Recurso</th>
                        <th>Proveedor</th>
                        <th>External ID</th>
                        <th>Título</th>
                        <th>Fecha / rango</th>
                        <th>Horario</th>
                        <th class="text-center">Día completo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ocupaciones as $ocupacion)
                        <tr>
                            <td class="align-top font-weight-semibold">{{ $ocupacion->negocio?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $ocupacion->recurso?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $ocupacion->proveedor ?: '—' }}</td>
                            <td class="align-top text-muted">{{ $ocupacion->external_id }}</td>
                            <td class="align-top">{{ $ocupacion->titulo ?: '—' }}</td>
                            <td class="align-top text-muted">
                                @if($ocupacion->inicio_datetime && $ocupacion->fin_datetime)
                                    {{ $ocupacion->inicio_datetime->format('d/m/Y') }} → {{ $ocupacion->fin_datetime->format('d/m/Y') }}
                                @elseif($ocupacion->fecha)
                                    {{ $ocupacion->fecha->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="align-top text-muted">
                                @if($ocupacion->es_dia_completo)
                                    <span class="badge badge-light border">Día completo</span>
                                @elseif($ocupacion->hora_inicio && $ocupacion->hora_fin)
                                    {{ substr((string) $ocupacion->hora_inicio, 0, 5) }} - {{ substr((string) $ocupacion->hora_fin, 0, 5) }}
                                @elseif($ocupacion->inicio_datetime && $ocupacion->fin_datetime)
                                    {{ $ocupacion->inicio_datetime->format('H:i') }} - {{ $ocupacion->fin_datetime->format('H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $ocupacion->es_dia_completo ? 'badge-warning' : 'badge-light border' }}">
                                    {{ $ocupacion->es_dia_completo ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.ocupaciones-externas.show', $ocupacion) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.ocupaciones-externas.edit', $ocupacion) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.ocupaciones-externas.destroy', $ocupacion) }}"
                                    data-message="Vas a eliminar esta ocupación externa."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No hay ocupaciones externas registradas con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ocupaciones->hasPages())
            <div class="card-footer bg-white">
                {{ $ocupaciones->links('pagination::bootstrap-4') }}
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

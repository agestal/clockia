@extends('layouts.app')

@section('title', 'Mapeos de integración')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Mapeos de integración</h1>
            <p class="text-muted mb-0">Gestiona los mapeos entre recursos externos e internos del sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.integracion-mapeos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo mapeo de integración
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.integracion-mapeos.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre externo, external ID o integración">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="created_at" @selected($sort === 'created_at')>Alta</option>
                            <option value="nombre_externo" @selected($sort === 'nombre_externo')>Nombre externo</option>
                            <option value="external_id" @selected($sort === 'external_id')>External ID</option>
                        </select>
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="direction">Dirección</label>
                        <select id="direction" name="direction" class="form-control">
                            <option value="asc" @selected($direction === 'asc')>Ascendente</option>
                            <option value="desc" @selected($direction === 'desc')>Descendente</option>
                        </select>
                    </div>

                    <div class="form-group col-md-12 col-lg-3 mb-0 d-flex justify-content-lg-end mt-3 mt-lg-0">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('admin.integracion-mapeos.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Integración</th>
                        <th>Tipo origen</th>
                        <th>External ID</th>
                        <th>Nombre externo</th>
                        <th>Destino</th>
                        <th class="text-center">Activo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapeos as $mapeo)
                        <tr>
                            <td class="align-top font-weight-semibold">{{ $mapeo->integracion?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $mapeo->tipo_origen }}</td>
                            <td class="align-top text-muted">{{ $mapeo->external_id }}</td>
                            <td class="align-top text-muted">{{ $mapeo->nombre_externo ?: '—' }}</td>
                            <td class="align-top">
                                @if($mapeo->negocio)
                                    <span class="badge badge-info">Negocio</span> {{ $mapeo->negocio->nombre }}
                                @endif
                                @if($mapeo->recurso)
                                    <span class="badge badge-primary">Recurso</span> {{ $mapeo->recurso->nombre }}
                                @endif
                                @if($mapeo->servicio)
                                    <span class="badge badge-warning">Servicio</span> {{ $mapeo->servicio->nombre }}
                                @endif
                                @if(!$mapeo->negocio && !$mapeo->recurso && !$mapeo->servicio)
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $mapeo->activo ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $mapeo->activo ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.integracion-mapeos.show', $mapeo) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.integracion-mapeos.edit', $mapeo) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.integracion-mapeos.destroy', $mapeo) }}"
                                    data-message="Vas a eliminar el mapeo &quot;{{ e($mapeo->external_id) }}&quot;."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No hay mapeos de integración registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mapeos->hasPages())
            <div class="card-footer bg-white">
                {{ $mapeos->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    @include('admin.partials.delete-modal')
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], input[type="email"]').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });

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

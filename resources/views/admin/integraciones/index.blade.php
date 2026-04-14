@extends('layouts.app')

@section('title', 'Integraciones')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Integraciones</h1>
            <p class="text-muted mb-0">Gestiona las integraciones con proveedores externos.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.integraciones.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nueva integración
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.integraciones.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre, proveedor o negocio">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="nombre" @selected($sort === 'nombre')>Nombre</option>
                            <option value="created_at" @selected($sort === 'created_at')>Alta</option>
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
                        <a href="{{ route('admin.integraciones.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Negocio</th>
                        <th>Proveedor</th>
                        <th>Modo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center">Cuentas</th>
                        <th class="text-center">Mapeos</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($integraciones as $integracion)
                        <tr data-row-id="{{ $integracion->id }}">
                            <td class="align-top font-weight-semibold">{{ $integracion->nombre }}</td>
                            <td class="align-top">{{ $integracion->negocio?->nombre ?: '—' }}</td>
                            <td class="align-top">
                                @switch($integracion->proveedor)
                                    @case('google_calendar') Google Calendar @break
                                    @default {{ $integracion->proveedor }}
                                @endswitch
                            </td>
                            <td class="align-top">
                                @switch($integracion->modo_operacion)
                                    @case('solo_clockia') Solo Clockia @break
                                    @case('coexistencia') Coexistencia @break
                                    @case('migracion') Migración @break
                                    @default {{ $integracion->modo_operacion }}
                                @endswitch
                            </td>
                            <td class="text-center align-top">
                                @switch($integracion->estado)
                                    @case('pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                        @break
                                    @case('conectada')
                                        <span class="badge badge-success">Conectada</span>
                                        @break
                                    @case('error')
                                        <span class="badge badge-danger">Error</span>
                                        @break
                                    @case('desactivada')
                                        <span class="badge badge-secondary">Desactivada</span>
                                        @break
                                    @default
                                        <span class="badge badge-light border">{{ $integracion->estado }}</span>
                                @endswitch
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $integracion->activo ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $integracion->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $integracion->cuentas_count }}</span></td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $integracion->mapeos_count }}</span></td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.integraciones.show', $integracion) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.integraciones.edit', $integracion) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.integraciones.destroy', $integracion) }}"
                                    data-message="Vas a eliminar la integración &quot;{{ e($integracion->nombre) }}&quot;."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No hay integraciones registradas con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($integraciones->hasPages())
            <div class="card-footer bg-white">
                {{ $integraciones->links('pagination::bootstrap-4') }}
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
                    form.querySelectorAll('input[type="text"]').forEach((field) => {
                        field.value = field.value.replace(/\s+/g, ' ').trim();
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

@extends('layouts.app')

@section('title', 'Combinaciones de recursos')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Combinaciones de recursos</h1>
            <p class="text-muted mb-0">Gestiona qué recursos se pueden combinar entre sí.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.recurso-combinaciones.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nueva combinación
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.recurso-combinaciones.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre de recurso">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
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
                        <a href="{{ route('admin.recurso-combinaciones.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Recurso</th>
                        <th>Recurso combinado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combinaciones as $combinacion)
                        <tr>
                            <td class="align-top font-weight-semibold">{{ $combinacion->recurso?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $combinacion->recursoCombinado?->nombre ?: '—' }}</td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.recurso-combinaciones.edit', $combinacion) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.recurso-combinaciones.destroy', $combinacion) }}"
                                    data-message="Vas a eliminar esta combinación de recursos."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">No hay combinaciones registradas con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($combinaciones->hasPages())
            <div class="card-footer bg-white">
                {{ $combinaciones->links('pagination::bootstrap-4') }}
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

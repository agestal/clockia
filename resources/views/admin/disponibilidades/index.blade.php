@extends('layouts.app')

@section('title', 'Disponibilidades')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Disponibilidades</h1>
            <p class="text-muted mb-0">Gestiona las franjas horarias disponibles de los recursos.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.disponibilidades.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nueva disponibilidad
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.disponibilidades.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por recurso o día de la semana">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="dia_semana" @selected($sort === 'dia_semana')>Día</option>
                            <option value="hora_inicio" @selected($sort === 'hora_inicio')>Hora inicio</option>
                            <option value="hora_fin" @selected($sort === 'hora_fin')>Hora fin</option>
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
                        <a href="{{ route('admin.disponibilidades.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Recurso</th>
                        <th>Día</th>
                        <th>Hora inicio</th>
                        <th>Hora fin</th>
                        <th>Turno</th>
                        <th class="text-center">Buffer</th>
                        <th class="text-center">Activa</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disponibilidades as $disponibilidad)
                        <tr data-row-id="{{ $disponibilidad->id }}">
                            <td class="align-top font-weight-semibold">{{ $disponibilidad->recurso?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $dayOptions[$disponibilidad->dia_semana] ?? $disponibilidad->dia_semana }}</td>
                            <td class="align-top">{{ substr((string) $disponibilidad->hora_inicio, 0, 5) }}</td>
                            <td class="align-top">{{ substr((string) $disponibilidad->hora_fin, 0, 5) }}</td>
                            <td class="align-top">{{ $disponibilidad->nombre_turno ?: '—' }}</td>
                            <td class="text-center align-top">{{ $disponibilidad->buffer_minutos !== null ? $disponibilidad->buffer_minutos . ' min' : '—' }}</td>
                            <td class="text-center align-top">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input js-inline-activo"
                                        id="activo-{{ $disponibilidad->id }}"
                                        data-url="{{ route('admin.disponibilidades.inline-update', $disponibilidad) }}"
                                        data-current="{{ $disponibilidad->activo ? '1' : '0' }}"
                                        @checked($disponibilidad->activo)
                                    >
                                    <label class="custom-control-label" for="activo-{{ $disponibilidad->id }}"></label>
                                </div>
                                <div class="small text-muted mt-1 js-inline-activo-label">{{ $disponibilidad->activo ? 'Activa' : 'Inactiva' }}</div>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.disponibilidades.show', $disponibilidad) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.disponibilidades.edit', $disponibilidad) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.disponibilidades.destroy', $disponibilidad) }}"
                                    data-message="Vas a eliminar esta disponibilidad."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No hay disponibilidades registradas con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($disponibilidades->hasPages())
            <div class="card-footer bg-white">
                {{ $disponibilidades->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    @include('admin.partials.delete-modal')
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-inline-activo').forEach((toggle) => {
                toggle.addEventListener('change', async () => {
                    const checked = toggle.checked;
                    const row = toggle.closest('tr');
                    const label = row.querySelector('.js-inline-activo-label');
                    const previous = toggle.dataset.current === '1';

                    const formData = new FormData();
                    formData.append('_method', 'PATCH');
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('field', 'activo');
                    formData.append('value', checked ? '1' : '0');

                    toggle.disabled = true;

                    try {
                        const response = await fetch(toggle.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        });

                        const payload = await response.json();

                        if (! response.ok) {
                            throw payload;
                        }

                        toggle.dataset.current = payload.data.activo ? '1' : '0';
                        label.textContent = payload.data.activo_label;
                    } catch (payload) {
                        toggle.checked = previous;
                        label.textContent = previous ? 'Activa' : 'Inactiva';
                        window.alert(payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el estado de la disponibilidad.');
                    } finally {
                        toggle.disabled = false;
                    }
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

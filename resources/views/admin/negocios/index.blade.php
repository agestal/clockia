@extends('layouts.app')

@section('title', 'Negocios')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Negocios</h1>
            <p class="text-muted mb-0">Gestiona los negocios configurados en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.negocios.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo negocio
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.negocios.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre, email, teléfono o zona horaria">
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
                        <a href="{{ route('admin.negocios.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo de negocio</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Zona horaria</th>
                        <th class="text-center">Servicios</th>
                        <th class="text-center">Recursos</th>
                        <th class="text-center">Reservas</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center">Modificación</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($negocios as $negocio)
                        <tr data-row-id="{{ $negocio->id }}">
                            <td class="align-top font-weight-semibold">{{ $negocio->nombre }}</td>
                            <td class="align-top">{{ $negocio->tipoNegocio?->nombre ?: '—' }}</td>
                            <td class="align-top text-muted">{{ $negocio->email ?: 'Sin email' }}</td>
                            <td class="align-top text-muted">{{ $negocio->telefono ?: 'Sin teléfono' }}</td>
                            <td class="align-top text-muted">{{ $negocio->zona_horaria }}</td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $negocio->servicios_count }}</span></td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $negocio->recursos_count }}</span></td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $negocio->reservas_count }}</span></td>
                            <td class="text-center align-top">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input js-inline-activo"
                                        id="activo-{{ $negocio->id }}"
                                        data-url="{{ route('admin.negocios.inline-update', $negocio) }}"
                                        data-current="{{ $negocio->activo ? '1' : '0' }}"
                                        @checked($negocio->activo)
                                    >
                                    <label class="custom-control-label" for="activo-{{ $negocio->id }}"></label>
                                </div>
                                <div class="small text-muted mt-1 js-inline-activo-label">{{ $negocio->activo ? 'Activo' : 'Inactivo' }}</div>
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $negocio->permite_modificacion ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $negocio->permite_modificacion ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.negocios.show', $negocio) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.negocios.edit', $negocio) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.negocios.destroy', $negocio) }}"
                                    data-message="Vas a eliminar el negocio &quot;{{ e($negocio->nombre) }}&quot;."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">No hay negocios registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($negocios->hasPages())
            <div class="card-footer bg-white">
                {{ $negocios->links('pagination::bootstrap-4') }}
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
                        label.textContent = previous ? 'Activo' : 'Inactivo';
                        window.alert(payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el estado del negocio.');
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

@extends('layouts.app')

@section('title', 'Servicios')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Servicios</h1>
            <p class="text-muted mb-0">Gestiona el catálogo de servicios y sus recursos asociados.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.servicios.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo servicio
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.servicios.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre, negocio o tipo de precio">
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
                        <a href="{{ route('admin.servicios.index') }}" class="btn btn-light border">Limpiar</a>
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
                        <th>Tipo de precio</th>
                        <th class="text-center">Duración</th>
                        <th class="text-center">Precio base</th>
                        <th class="text-center">Recursos</th>
                        <th class="text-center">Reservas</th>
                        <th class="text-center">Requiere pago</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center">Reembolsable</th>
                        <th class="text-center">Precio/tiempo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servicios as $servicio)
                        <tr data-row-id="{{ $servicio->id }}">
                            <td class="align-top font-weight-semibold">{{ $servicio->nombre }}</td>
                            <td class="align-top">{{ $servicio->negocio?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $servicio->tipoPrecio?->nombre ?: '—' }}</td>
                            <td class="text-center align-top">{{ $servicio->duracion_minutos }} min</td>
                            <td class="text-center align-top" style="min-width: 210px;">
                                <div class="js-inline-wrapper">
                                    <div class="d-flex align-items-start justify-content-center">
                                        <div class="js-inline-display-precio">{{ number_format((float) $servicio->precio_base, 2, ',', '.') }}</div>
                                        <button type="button" class="btn btn-xs btn-light border ml-2 js-inline-edit-toggle" data-target="inline-form-precio-{{ $servicio->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                    <form id="inline-form-precio-{{ $servicio->id }}" action="{{ route('admin.servicios.inline-update', $servicio) }}" method="POST" class="js-inline-form d-none mt-2" data-field="precio_base" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="precio_base">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="value" value="{{ $servicio->precio_base }}" min="0" max="99999999.99" step="0.01" class="form-control js-inline-input">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <button type="button" class="btn btn-light border js-inline-cancel">Cancelar</button>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block js-inline-error d-none mt-1"></div>
                                    </form>
                                </div>
                            </td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $servicio->recursos_count }}</span></td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $servicio->reservas_count }}</span></td>
                            <td class="text-center align-top">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input js-inline-toggle"
                                        id="requiere-pago-{{ $servicio->id }}"
                                        data-url="{{ route('admin.servicios.inline-update', $servicio) }}"
                                        data-field="requiere_pago"
                                        data-current="{{ $servicio->requiere_pago ? '1' : '0' }}"
                                        @checked($servicio->requiere_pago)
                                    >
                                    <label class="custom-control-label" for="requiere-pago-{{ $servicio->id }}"></label>
                                </div>
                                <div class="small text-muted mt-1 js-inline-requiere-pago-label">{{ $servicio->requiere_pago ? 'Sí' : 'No' }}</div>
                            </td>
                            <td class="text-center align-top">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input js-inline-toggle"
                                        id="activo-{{ $servicio->id }}"
                                        data-url="{{ route('admin.servicios.inline-update', $servicio) }}"
                                        data-field="activo"
                                        data-current="{{ $servicio->activo ? '1' : '0' }}"
                                        @checked($servicio->activo)
                                    >
                                    <label class="custom-control-label" for="activo-{{ $servicio->id }}"></label>
                                </div>
                                <div class="small text-muted mt-1 js-inline-activo-label">{{ $servicio->activo ? 'Activo' : 'Inactivo' }}</div>
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $servicio->es_reembolsable ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $servicio->es_reembolsable ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-center align-top">
                                <span class="badge {{ $servicio->precio_por_unidad_tiempo ? 'badge-info' : 'badge-secondary' }}">
                                    {{ $servicio->precio_por_unidad_tiempo ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.servicios.show', $servicio) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.servicios.edit', $servicio) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.servicios.destroy', $servicio) }}"
                                    data-message="Vas a eliminar el servicio &quot;{{ e($servicio->nombre) }}&quot;."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">No hay servicios registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($servicios->hasPages())
            <div class="card-footer bg-white">
                {{ $servicios->links('pagination::bootstrap-4') }}
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

            document.querySelectorAll('.js-inline-edit-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = document.getElementById(button.dataset.target);
                    const input = form.querySelector('.js-inline-input');
                    form.classList.remove('d-none');
                    input.focus();
                    input.select();
                });
            });

            document.querySelectorAll('.js-inline-cancel').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = button.closest('.js-inline-form');
                    const input = form.querySelector('.js-inline-input');
                    const error = form.querySelector('.js-inline-error');
                    form.classList.add('d-none');
                    input.classList.remove('is-invalid');
                    error.classList.add('d-none');
                    error.textContent = '';
                });
            });

            document.querySelectorAll('.js-inline-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const input = form.querySelector('.js-inline-input');
                    const error = form.querySelector('.js-inline-error');
                    const wrapper = form.closest('.js-inline-wrapper');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalLabel = submitButton.textContent;

                    input.value = input.value.trim();
                    input.classList.remove('is-invalid');
                    error.classList.add('d-none');
                    error.textContent = '';

                    if (input.value === '' || Number.isNaN(Number(input.value)) || Number(input.value) < 0) {
                        input.classList.add('is-invalid');
                        error.textContent = 'El precio base debe ser un número válido mayor o igual que 0.';
                        error.classList.remove('d-none');
                        input.focus();
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Guardando...';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw payload;
                        }

                        wrapper.querySelector('.js-inline-display-precio').textContent = payload.data.precio_base_label;
                        input.value = payload.data.precio_base;
                        form.classList.add('d-none');
                    } catch (payload) {
                        const message = payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el precio base.';
                        input.classList.add('is-invalid');
                        error.textContent = message;
                        error.classList.remove('d-none');
                        input.focus();
                    } finally {
                        submitButton.disabled = false;
                        submitButton.textContent = originalLabel;
                    }
                });
            });

            document.querySelectorAll('.js-inline-toggle').forEach((toggle) => {
                toggle.addEventListener('change', async () => {
                    const checked = toggle.checked;
                    const row = toggle.closest('tr');
                    const field = toggle.dataset.field;
                    const previous = toggle.dataset.current === '1';

                    const formData = new FormData();
                    formData.append('_method', 'PATCH');
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('field', field);
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

                        if (!response.ok) {
                            throw payload;
                        }

                        toggle.dataset.current = checked ? '1' : '0';

                        if (field === 'requiere_pago') {
                            row.querySelector('.js-inline-requiere-pago-label').textContent = payload.data.requiere_pago_label;
                        }

                        if (field === 'activo') {
                            row.querySelector('.js-inline-activo-label').textContent = payload.data.activo_label;
                        }
                    } catch (payload) {
                        toggle.checked = previous;

                        if (field === 'requiere_pago') {
                            row.querySelector('.js-inline-requiere-pago-label').textContent = previous ? 'Sí' : 'No';
                        }

                        if (field === 'activo') {
                            row.querySelector('.js-inline-activo-label').textContent = previous ? 'Activo' : 'Inactivo';
                        }

                        window.alert(payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el campo.');
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

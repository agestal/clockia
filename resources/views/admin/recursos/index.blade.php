@extends('layouts.app')

@section('title', 'Recursos')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Recursos</h1>
            <p class="text-muted mb-0">Gestiona los recursos operativos disponibles en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.recursos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo recurso
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.recursos.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre, negocio o tipo de recurso">
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
                        <a href="{{ route('admin.recursos.index') }}" class="btn btn-light border">Limpiar</a>
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
                        <th>Tipo de recurso</th>
                        <th class="text-center">Capacidad</th>
                        <th class="text-center">Combinable</th>
                        <th class="text-center">Disponibilidades</th>
                        <th class="text-center">Bloqueos</th>
                        <th class="text-center">Reservas</th>
                        <th class="text-center">Activo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recursos as $recurso)
                        <tr data-row-id="{{ $recurso->id }}">
                            <td class="align-top font-weight-semibold">{{ $recurso->nombre }}</td>
                            <td class="align-top">{{ $recurso->negocio?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $recurso->tipoRecurso?->nombre ?: '—' }}</td>
                            <td class="text-center align-top" style="min-width: 200px;">
                                <div class="js-inline-wrapper">
                                    <div class="d-flex align-items-start justify-content-center">
                                        <div class="js-inline-display-capacidad">{{ $recurso->capacidad ?: 'Sin definir' }}</div>
                                        <button type="button" class="btn btn-xs btn-light border ml-2 js-inline-edit-toggle" data-target="inline-form-capacidad-{{ $recurso->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                    <form id="inline-form-capacidad-{{ $recurso->id }}" action="{{ route('admin.recursos.inline-update', $recurso) }}" method="POST" class="js-inline-form d-none mt-2" data-field="capacidad" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="capacidad">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="value" value="{{ $recurso->capacidad }}" min="1" step="1" class="form-control js-inline-input">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <button type="button" class="btn btn-light border js-inline-cancel">Cancelar</button>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-1">Déjalo vacío para guardar sin capacidad.</div>
                                        <div class="invalid-feedback d-block js-inline-error d-none mt-1"></div>
                                    </form>
                                </div>
                            </td>
                            <td class="text-center align-top">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input js-inline-combinable"
                                        id="combinable-{{ $recurso->id }}"
                                        data-url="{{ route('admin.recursos.inline-update', $recurso) }}"
                                        data-current="{{ $recurso->combinable ? '1' : '0' }}"
                                        @checked($recurso->combinable)
                                    >
                                    <label class="custom-control-label" for="combinable-{{ $recurso->id }}"></label>
                                </div>
                                <div class="small text-muted mt-1 js-inline-combinable-label">{{ $recurso->combinable ? 'Sí' : 'No' }}</div>
                            </td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $recurso->disponibilidades_count }}</span></td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $recurso->bloqueos_count }}</span></td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $recurso->reservas_count }}</span></td>
                            <td class="text-center align-top">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input js-inline-activo"
                                        id="activo-{{ $recurso->id }}"
                                        data-url="{{ route('admin.recursos.inline-update', $recurso) }}"
                                        data-current="{{ $recurso->activo ? '1' : '0' }}"
                                        @checked($recurso->activo)
                                    >
                                    <label class="custom-control-label" for="activo-{{ $recurso->id }}"></label>
                                </div>
                                <div class="small text-muted mt-1 js-inline-activo-label">{{ $recurso->activo ? 'Activo' : 'Inactivo' }}</div>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.recursos.show', $recurso) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.recursos.edit', $recurso) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.recursos.destroy', $recurso) }}"
                                    data-message="Vas a eliminar el recurso &quot;{{ e($recurso->nombre) }}&quot;."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">No hay recursos registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recursos->hasPages())
            <div class="card-footer bg-white">
                {{ $recursos->links('pagination::bootstrap-4') }}
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

                    const field = form.dataset.field;
                    const input = form.querySelector('.js-inline-input');
                    const error = form.querySelector('.js-inline-error');
                    const wrapper = form.closest('.js-inline-wrapper');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalLabel = submitButton.textContent;

                    input.value = input.value.trim();
                    input.classList.remove('is-invalid');
                    error.classList.add('d-none');
                    error.textContent = '';

                    if (field === 'capacidad' && input.value !== '' && !/^\d+$/.test(input.value)) {
                        input.classList.add('is-invalid');
                        error.textContent = 'La capacidad debe ser un número entero válido.';
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

                        wrapper.querySelector('.js-inline-display-capacidad').textContent = payload.data.capacidad_label;
                        input.value = payload.data.capacidad || '';
                        form.classList.add('d-none');
                    } catch (payload) {
                        const message = payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar la capacidad.';
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

                        if (!response.ok) {
                            throw payload;
                        }

                        toggle.dataset.current = payload.data.activo ? '1' : '0';
                        label.textContent = payload.data.activo_label;
                    } catch (payload) {
                        toggle.checked = previous;
                        label.textContent = previous ? 'Activo' : 'Inactivo';
                        window.alert(payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el estado del recurso.');
                    } finally {
                        toggle.disabled = false;
                    }
                });
            });

            document.querySelectorAll('.js-inline-combinable').forEach((toggle) => {
                toggle.addEventListener('change', async () => {
                    const checked = toggle.checked;
                    const row = toggle.closest('tr');
                    const label = row.querySelector('.js-inline-combinable-label');
                    const previous = toggle.dataset.current === '1';

                    const formData = new FormData();
                    formData.append('_method', 'PATCH');
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('field', 'combinable');
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

                        toggle.dataset.current = payload.data.combinable ? '1' : '0';
                        label.textContent = payload.data.combinable_label;
                    } catch (payload) {
                        toggle.checked = previous;
                        label.textContent = previous ? 'Sí' : 'No';
                        window.alert(payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el estado de combinable.');
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

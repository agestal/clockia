@extends('layouts.app')

@section('title', 'Clientes')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Clientes</h1>
            <p class="text-muted mb-0">Gestiona la base de clientes del sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo cliente
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.clientes.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por nombre, email, teléfono o notas">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="nombre" @selected($sort === 'nombre')>Nombre</option>
                            <option value="email" @selected($sort === 'email')>Email</option>
                            <option value="telefono" @selected($sort === 'telefono')>Teléfono</option>
                            <option value="created_at" @selected($sort === 'created_at')>Alta</option>
                            <option value="updated_at" @selected($sort === 'updated_at')>Actualización</option>
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
                        <a href="{{ route('admin.clientes.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th class="text-center">Reservas</th>
                        <th class="text-nowrap">Actualizado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr data-row-id="{{ $cliente->id }}">
                            <td class="align-top" style="min-width: 260px;">
                                <div class="js-inline-wrapper">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="pr-2">
                                            <div class="font-weight-semibold js-inline-display-nombre">{{ $cliente->nombre }}</div>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-light border js-inline-edit-toggle" data-target="inline-form-nombre-{{ $cliente->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                    <form id="inline-form-nombre-{{ $cliente->id }}" action="{{ route('admin.clientes.inline-update', $cliente) }}" method="POST" class="js-inline-form d-none mt-2" data-field="nombre" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="nombre">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="value" value="{{ $cliente->nombre }}" maxlength="255" minlength="2" required class="form-control js-inline-input">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <button type="button" class="btn btn-light border js-inline-cancel">Cancelar</button>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block js-inline-error d-none mt-1"></div>
                                    </form>
                                </div>
                            </td>
                            <td class="align-top text-muted">{{ $cliente->email ?: 'Sin email' }}</td>
                            <td class="align-top" style="min-width: 220px;">
                                <div class="js-inline-wrapper">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="pr-2">
                                            <div class="text-muted js-inline-display-telefono">{{ $cliente->telefono ?: 'Sin teléfono' }}</div>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-light border js-inline-edit-toggle" data-target="inline-form-telefono-{{ $cliente->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                    <form id="inline-form-telefono-{{ $cliente->id }}" action="{{ route('admin.clientes.inline-update', $cliente) }}" method="POST" class="js-inline-form d-none mt-2" data-field="telefono" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="telefono">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="value" value="{{ $cliente->telefono }}" maxlength="255" class="form-control js-inline-input">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <button type="button" class="btn btn-light border js-inline-cancel">Cancelar</button>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block js-inline-error d-none mt-1"></div>
                                    </form>
                                </div>
                            </td>
                            <td class="text-center align-top">
                                <span class="badge badge-light border">{{ $cliente->reservas_count }}</span>
                            </td>
                            <td class="text-nowrap text-muted align-top">{{ optional($cliente->updated_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button type="button" class="btn btn-xs btn-outline-danger js-delete-button" data-action="{{ route('admin.clientes.destroy', $cliente) }}" data-message="Vas a eliminar el cliente &quot;{{ e($cliente->nombre) }}&quot;.">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No hay clientes registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
            <div class="card-footer bg-white">
                {{ $clientes->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    @include('admin.partials.delete-modal')
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('.js-basic-validation-form, .js-inline-form');

            forms.forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach((field) => {
                        field.value = field.value.trim();
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

                    if (field === 'nombre' && input.value.length < 2) {
                        input.classList.add('is-invalid');
                        error.textContent = 'El nombre debe tener al menos 2 caracteres.';
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

                        if (! response.ok) {
                            throw payload;
                        }

                        if (field === 'nombre') {
                            wrapper.querySelector('.js-inline-display-nombre').textContent = payload.data.nombre;
                        }

                        if (field === 'telefono') {
                            wrapper.querySelector('.js-inline-display-telefono').textContent = payload.data.telefono || 'Sin teléfono';
                        }

                        input.value = field === 'telefono' ? (payload.data.telefono || '') : payload.data.nombre;
                        form.classList.add('d-none');
                    } catch (payload) {
                        const message = payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el campo.';
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

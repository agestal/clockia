@extends('layouts.app')

@section('title', 'Reservas')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Reservas</h1>
            <p class="text-muted mb-0">Gestiona las reservas operativas del sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.reservas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nueva reserva
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.reservas.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por fecha, cliente, negocio, servicio, recurso o estado">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="fecha" @selected($sort === 'fecha')>Fecha</option>
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
                        <a href="{{ route('admin.reservas.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Localizador</th>
                        <th>Hora inicio</th>
                        <th>Hora fin</th>
                        <th>Cliente</th>
                        <th>Negocio</th>
                        <th>Servicio</th>
                        <th>Recurso</th>
                        <th>Estado</th>
                        <th class="text-center">Precio final</th>
                        <th class="text-center">Pagos</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr data-row-id="{{ $reserva->id }}">
                            <td class="align-top">{{ optional($reserva->fecha)->format('d/m/Y') }}</td>
                            <td class="align-top"><code>{{ $reserva->localizador ?: '—' }}</code></td>
                            <td class="align-top">{{ substr((string) $reserva->hora_inicio, 0, 5) }}</td>
                            <td class="align-top">{{ substr((string) $reserva->hora_fin, 0, 5) }}</td>
                            <td class="align-top">{{ $reserva->cliente?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $reserva->negocio?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $reserva->servicio?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $reserva->recurso?->nombre ?: '—' }}</td>
                            <td class="align-top" style="min-width: 260px;">
                                <div class="js-inline-wrapper">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="js-inline-display-estado">{{ $reserva->estadoReserva?->nombre ?: '—' }}</div>
                                        <button type="button" class="btn btn-xs btn-light border js-inline-edit-toggle" data-target="inline-form-estado-{{ $reserva->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                    <form id="inline-form-estado-{{ $reserva->id }}" action="{{ route('admin.reservas.inline-update', $reserva) }}" method="POST" class="js-inline-form d-none mt-2" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="estado_reserva_id">
                                        <div class="input-group input-group-sm">
                                            <select name="value" class="form-control js-inline-estado-select">
                                                @foreach($estadoReservaOptions as $estadoReserva)
                                                    <option value="{{ $estadoReserva->id }}" @selected($reserva->estado_reserva_id === $estadoReserva->id)>{{ $estadoReserva->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <button type="button" class="btn btn-light border js-inline-cancel">Cancelar</button>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback d-block js-inline-error d-none mt-1"></div>
                                    </form>
                                </div>
                            </td>
                            <td class="text-center align-top">{{ number_format((float) $reserva->precio_final, 2, ',', '.') }}</td>
                            <td class="text-center align-top"><span class="badge badge-light border">{{ $reserva->pagos_count }}</span></td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.reservas.show', $reserva) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.reservas.edit', $reserva) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.reservas.destroy', $reserva) }}"
                                    data-message="Vas a eliminar la reserva #{{ $reserva->id }}."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">No hay reservas registradas con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reservas->hasPages())
            <div class="card-footer bg-white">
                {{ $reservas->links('pagination::bootstrap-4') }}
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
                        field.value = field.value.trim();
                    });
                });
            });

            document.querySelectorAll('.js-inline-edit-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = document.getElementById(button.dataset.target);
                    const select = form.querySelector('.js-inline-estado-select');
                    form.classList.remove('d-none');
                    select.focus();
                });
            });

            document.querySelectorAll('.js-inline-cancel').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = button.closest('.js-inline-form');
                    const error = form.querySelector('.js-inline-error');
                    form.classList.add('d-none');
                    error.classList.add('d-none');
                    error.textContent = '';
                });
            });

            document.querySelectorAll('.js-inline-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const select = form.querySelector('.js-inline-estado-select');
                    const error = form.querySelector('.js-inline-error');
                    const wrapper = form.closest('.js-inline-wrapper');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalLabel = submitButton.textContent;

                    error.classList.add('d-none');
                    error.textContent = '';

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

                        wrapper.querySelector('.js-inline-display-estado').textContent = payload.data.estado_reserva_label;
                        form.classList.add('d-none');
                    } catch (payload) {
                        const message = payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el estado de la reserva.';
                        error.textContent = message;
                        error.classList.remove('d-none');
                        select.focus();
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

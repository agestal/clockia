@extends('layouts.app')

@section('title', 'Pagos')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Pagos</h1>
            <p class="text-muted mb-0">Gestiona los pagos registrados en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.pagos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Nuevo pago
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.pagos.index') }}" class="js-basic-validation-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-6 col-lg-5 mb-md-0">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control" placeholder="Buscar por referencia, importe, estado, tipo o reserva">
                    </div>

                    <div class="form-group col-md-3 col-lg-2 mb-md-0">
                        <label for="sort">Ordenar por</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="created_at" @selected($sort === 'created_at')>Alta</option>
                            <option value="importe" @selected($sort === 'importe')>Importe</option>
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
                        <a href="{{ route('admin.pagos.index') }}" class="btn btn-light border">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Reserva</th>
                        <th>Tipo de pago</th>
                        <th>Concepto</th>
                        <th>Estado de pago</th>
                        <th class="text-center">Importe</th>
                        <th>Fecha de pago</th>
                        <th>Referencia externa</th>
                        <th class="text-center">Bot</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        <tr data-row-id="{{ $pago->id }}">
                            <td class="align-top">
                                @if($pago->reserva)
                                    <div class="font-weight-semibold">Reserva #{{ $pago->reserva->id }}</div>
                                    <div class="small text-muted">
                                        {{ optional($pago->reserva->fecha)->format('d/m/Y') }}
                                        @if($pago->reserva->hora_inicio)
                                            · {{ substr((string) $pago->reserva->hora_inicio, 0, 5) }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-top">{{ $pago->tipoPago?->nombre ?: '—' }}</td>
                            <td class="align-top">{{ $pago->conceptoPago?->nombre ?: '—' }}</td>
                            <td class="align-top" style="min-width: 260px;">
                                <div class="js-inline-wrapper">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="js-inline-display-estado">{{ $pago->estadoPago?->nombre ?: '—' }}</div>
                                        <button type="button" class="btn btn-xs btn-light border js-inline-edit-toggle" data-target="inline-form-estado-{{ $pago->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                    <form id="inline-form-estado-{{ $pago->id }}" action="{{ route('admin.pagos.inline-update', $pago) }}" method="POST" class="js-inline-form d-none mt-2" novalidate>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="field" value="estado_pago_id">
                                        <div class="input-group input-group-sm">
                                            <select name="value" class="form-control js-inline-estado-select">
                                                @foreach($estadoPagoOptions as $estadoPago)
                                                    <option value="{{ $estadoPago->id }}" @selected($pago->estado_pago_id === $estadoPago->id)>{{ $estadoPago->nombre }}</option>
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
                            <td class="text-center align-top">{{ number_format((float) $pago->importe, 2, ',', '.') }}</td>
                            <td class="align-top text-muted">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'Sin registrar' }}</td>
                            <td class="align-top text-muted">{{ $pago->referencia_externa ?: 'Sin referencia' }}</td>
                            <td class="text-center align-top">
                                <span class="badge {{ $pago->iniciado_por_bot ? 'badge-info' : 'badge-secondary' }}">
                                    {{ $pago->iniciado_por_bot ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap align-top">
                                <a href="{{ route('admin.pagos.show', $pago) }}" class="btn btn-xs btn-light border">Ver</a>
                                <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-xs btn-light border">Editar</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-danger js-delete-button"
                                    data-action="{{ route('admin.pagos.destroy', $pago) }}"
                                    data-message="Vas a eliminar el pago #{{ $pago->id }}."
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No hay pagos registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pagos->hasPages())
            <div class="card-footer bg-white">
                {{ $pagos->links('pagination::bootstrap-4') }}
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

                        wrapper.querySelector('.js-inline-display-estado').textContent = payload.data.estado_pago_label;
                        form.classList.add('d-none');
                    } catch (payload) {
                        const message = payload?.errors?.value?.[0] ?? payload?.message ?? 'No se ha podido actualizar el estado del pago.';
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

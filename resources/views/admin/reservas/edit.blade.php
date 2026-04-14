@extends('layouts.app')

@section('title', 'Editar reserva')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Editar reserva</h1>
        <p class="text-muted mb-0">Actualiza los datos principales de la reserva seleccionada.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.reservas.update', $reserva) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.reservas._form', [
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-basic-validation-form');
            const negocioSelect = window.jQuery('#negocio_id');
            const servicioSelect = window.jQuery('#servicio_id');
            const recursoSelect = window.jQuery('#recurso_id');

            form?.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"], textarea').forEach((field) => {
                    if (field.tagName === 'TEXTAREA') {
                        field.value = field.value.trim();
                        return;
                    }

                    field.value = field.value.replace(/\s+/g, ' ').trim();
                });
            });

            function currentNegocioId() {
                return negocioSelect.val() || '';
            }

            function resetDependentSelections() {
                servicioSelect.val(null).trigger('change');
                recursoSelect.val(null).trigger('change');
            }

            negocioSelect.on('change', () => {
                resetDependentSelections();
            });

            negocioSelect.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un negocio',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.negocios.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                    }),
                    processResults: data => data,
                },
            });

            servicioSelect.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un servicio',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.servicios.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                        negocio_id: currentNegocioId(),
                    }),
                    processResults: data => data,
                },
            });

            recursoSelect.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un recurso',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.recursos.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                        negocio_id: currentNegocioId(),
                    }),
                    processResults: data => data,
                },
            });

            window.jQuery('.js-select2-cliente').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un cliente',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.clientes.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                    }),
                    processResults: data => data,
                },
            });

            window.jQuery('.js-select2-estado-reserva').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un estado de reserva',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.estados-reserva.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                    }),
                    processResults: data => data,
                },
            });
        });
    </script>
@endpush

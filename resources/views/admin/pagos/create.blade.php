@extends('layouts.app')

@section('title', 'Nuevo pago')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo pago</h1>
        <p class="text-muted mb-0">Registra un nuevo pago asociado a una reserva.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.pagos.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.pagos._form', [
            'isEdit' => false,
            'submitLabel' => 'Crear pago',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-basic-validation-form');

            form?.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"]').forEach((field) => {
                    field.value = field.value.trim();
                });
            });

            window.jQuery('.js-select2-reserva').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona una reserva',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.reservas.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                    }),
                    processResults: data => data,
                },
            });

            window.jQuery('.js-select2-tipo-pago').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un tipo de pago',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.tipos-pago.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                    }),
                    processResults: data => data,
                },
            });

            window.jQuery('.js-select2-estado-pago').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un estado de pago',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.estados-pago.search') }}',
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

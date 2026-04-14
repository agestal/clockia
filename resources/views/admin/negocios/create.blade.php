@extends('layouts.app')

@section('title', 'Nuevo negocio')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo negocio</h1>
        <p class="text-muted mb-0">Crea un nuevo negocio para el sistema.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.negocios.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.negocios._form', [
            'negocio' => $negocio,
            'isEdit' => false,
            'submitLabel' => 'Guardar negocio',
            'selectedTipoNegocio' => $selectedTipoNegocio,
            'timezones' => $timezones,
        ])
    </form>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], input[type="email"]').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });

            $('.js-select2-tipo-negocio').select2({
                theme: 'bootstrap4',
                placeholder: 'Selecciona un tipo de negocio',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.tipos-negocio.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                        };
                    },
                    processResults: function (data) {
                        return data;
                    },
                },
            });

            $('.js-select2-timezone').select2({
                theme: 'bootstrap4',
                placeholder: 'Selecciona una zona horaria',
            });
        });
    </script>
@endpush

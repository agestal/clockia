@extends('layouts.app')

@section('title', 'Editar negocio')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar negocio</h1>
            <p class="text-muted mb-0">Actualiza la información principal del negocio.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.negocios.show', $negocio) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.negocios.update', $negocio) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.negocios._form', [
            'negocio' => $negocio,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
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

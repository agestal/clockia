@extends('layouts.app')

@section('title', 'Editar recurso')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Editar recurso</h1>
        <p class="text-muted mb-0">Actualiza los datos del recurso seleccionado.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.recursos.update', $recurso) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.recursos._form', [
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-basic-validation-form');

            form?.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"]').forEach((field) => {
                    field.value = field.value.replace(/\s+/g, ' ').trim();
                });

                const capacidad = form.querySelector('#capacidad');
                if (capacidad && capacidad.value.trim() === '') {
                    capacidad.value = '';
                }
            });

            window.jQuery('.js-select2-negocio').select2({
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

            window.jQuery('.js-select2-tipo-recurso').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un tipo de recurso',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.tipos-recurso.search') }}',
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

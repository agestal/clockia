@extends('layouts.app')

@section('title', 'Nuevo servicio')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo servicio</h1>
        <p class="text-muted mb-0">Crea un nuevo servicio y vincúlalo con los recursos necesarios.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.servicios.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.servicios._form', [
            'isEdit' => false,
            'submitLabel' => 'Crear servicio',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-basic-validation-form');

            form?.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"], textarea').forEach((field) => {
                    if (field.tagName === 'TEXTAREA') {
                        field.value = field.value.trim();
                        return;
                    }

                    field.value = field.value.replace(/\s+/g, ' ').trim();
                });
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

            window.jQuery('.js-select2-tipo-precio').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona un tipo de precio',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.tipos-precio.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1,
                    }),
                    processResults: data => data,
                },
            });

            window.jQuery('.js-select2-recursos').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecciona uno o varios recursos',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.ajax.recursos.search') }}',
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

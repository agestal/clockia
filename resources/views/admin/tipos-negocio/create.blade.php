@extends('layouts.app')

@section('title', 'Nuevo tipo de negocio')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo tipo de negocio</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de tipos de negocio.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.tipos-negocio.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-negocio._form', [
            'tipoNegocio' => $tipoNegocio,
            'isEdit' => false,
            'submitLabel' => 'Guardar tipo de negocio',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], textarea').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });
        });
    </script>
@endpush

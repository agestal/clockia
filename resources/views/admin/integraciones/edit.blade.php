@extends('layouts.app')

@section('title', 'Editar integración')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar integración</h1>
            <p class="text-muted mb-0">Actualiza la información de la integración.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.integraciones.show', $integracion) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.integraciones.update', $integracion) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.integraciones._form', [
            'integracion' => $integracion,
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
            });
        });
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar cliente</h1>
            <p class="text-muted mb-0">Actualiza la información del cliente.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.clientes._form', [
            'cliente' => $cliente,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });
        });
    </script>
@endpush

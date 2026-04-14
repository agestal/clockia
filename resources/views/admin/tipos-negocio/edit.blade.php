@extends('layouts.app')

@section('title', 'Editar tipo de negocio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar tipo de negocio</h1>
            <p class="text-muted mb-0">Actualiza la información base del tipo de negocio.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-negocio.show', $tipoNegocio) }}" class="btn btn-light border">
                Ver detalle
            </a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.tipos-negocio.update', $tipoNegocio) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-negocio._form', [
            'tipoNegocio' => $tipoNegocio,
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
                    form.querySelectorAll('input[type="text"], textarea').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });
        });
    </script>
@endpush

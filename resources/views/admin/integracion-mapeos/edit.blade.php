@extends('layouts.app')

@section('title', 'Editar mapeo de integración')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar mapeo de integración</h1>
            <p class="text-muted mb-0">Actualiza la información del mapeo de integración.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.integracion-mapeos.show', $mapeo) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.integracion-mapeos.update', $mapeo) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.integracion-mapeos._form', [
            'mapeo' => $mapeo,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
            'integraciones' => $integraciones,
            'negocios' => $negocios,
            'recursos' => $recursos,
            'servicios' => $servicios,
            'tipoOrigenOptions' => $tipoOrigenOptions,
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"]').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });
        });
    </script>
@endpush

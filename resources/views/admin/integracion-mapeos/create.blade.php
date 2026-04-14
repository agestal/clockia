@extends('layouts.app')

@section('title', 'Nuevo mapeo de integración')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo mapeo de integración</h1>
        <p class="text-muted mb-0">Crea un nuevo mapeo entre un recurso externo y uno interno.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.integracion-mapeos.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.integracion-mapeos._form', [
            'mapeo' => $mapeo,
            'isEdit' => false,
            'submitLabel' => 'Guardar mapeo',
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

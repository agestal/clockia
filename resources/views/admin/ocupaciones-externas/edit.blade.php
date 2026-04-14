@extends('layouts.app')

@section('title', 'Editar ocupación externa')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar ocupación externa</h1>
            <p class="text-muted mb-0">Actualiza la ocupación externa seleccionada.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.ocupaciones-externas.show', $ocupacion) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.ocupaciones-externas.update', $ocupacion) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.ocupaciones-externas._form', [
            'ocupacion' => $ocupacion,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
            'negocios' => $negocios,
            'recursos' => $recursos,
            'integraciones' => $integraciones,
            'proveedorOptions' => $proveedorOptions,
            'horaInicioValue' => $horaInicioValue,
            'horaFinValue' => $horaFinValue,
        ])
    </form>
@stop

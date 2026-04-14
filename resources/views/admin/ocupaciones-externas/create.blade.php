@extends('layouts.app')

@section('title', 'Nueva ocupación externa')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nueva ocupación externa</h1>
        <p class="text-muted mb-0">Crea una ocupación externa vinculada a un calendario externo.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.ocupaciones-externas.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.ocupaciones-externas._form', [
            'ocupacion' => $ocupacion,
            'isEdit' => false,
            'submitLabel' => 'Guardar ocupación externa',
            'negocios' => $negocios,
            'recursos' => $recursos,
            'integraciones' => $integraciones,
            'proveedorOptions' => $proveedorOptions,
            'horaInicioValue' => $horaInicioValue,
            'horaFinValue' => $horaFinValue,
        ])
    </form>
@stop

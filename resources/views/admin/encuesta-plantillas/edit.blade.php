@extends('layouts.app')

@section('title', 'Editar encuesta')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Editar encuesta</h1>
        <p class="text-muted mb-0">{{ $encuestaPlantilla->negocio?->nombre }} · {{ $encuestaPlantilla->nombre }}</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.encuesta-plantillas.update', $encuestaPlantilla) }}" method="POST" class="js-survey-template-form" novalidate>
        @include('admin.encuesta-plantillas._form', [
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
        ])
    </form>
@stop

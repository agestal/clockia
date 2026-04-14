@extends('layouts.app')

@section('title', 'Editar combinación de recursos')

@section('content_header_extra')
    <h1 class="mb-1">Editar combinación de recursos</h1>
    <p class="text-muted mb-0">Modifica la combinación entre recursos.</p>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form method="POST" action="{{ route('admin.recurso-combinaciones.update', $combinacion) }}" novalidate>
        @include('admin.recurso-combinaciones._form', [
            'combinacion' => $combinacion,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
        ])
    </form>
@stop

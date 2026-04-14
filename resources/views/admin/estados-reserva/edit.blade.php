@extends('layouts.app')

@section('title', 'Editar estado de reserva')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar estado de reserva</h1>
            <p class="text-muted mb-0">Actualiza la información base del estado de reserva.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.estados-reserva.show', $estadoReserva) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.estados-reserva.update', $estadoReserva) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.estados-reserva._form', ['estadoReserva' => $estadoReserva, 'isEdit' => true, 'submitLabel' => 'Guardar cambios'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

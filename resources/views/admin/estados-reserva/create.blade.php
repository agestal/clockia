@extends('layouts.app')

@section('title', 'Nuevo estado de reserva')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo estado de reserva</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de estados de reserva.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.estados-reserva.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.estados-reserva._form', ['estadoReserva' => $estadoReserva, 'isEdit' => false, 'submitLabel' => 'Guardar estado de reserva'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

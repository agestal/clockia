@extends('layouts.app')

@section('title', 'Nuevo tipo de bloqueo')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo tipo de bloqueo</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de tipos de bloqueo.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.tipos-bloqueo.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-bloqueo._form', ['tipoBloqueo' => $tipoBloqueo, 'isEdit' => false, 'submitLabel' => 'Guardar tipo de bloqueo'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

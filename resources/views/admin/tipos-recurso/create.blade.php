@extends('layouts.app')

@section('title', 'Nuevo tipo de recurso')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo tipo de recurso</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de tipos de recurso.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.tipos-recurso.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-recurso._form', ['tipoRecurso' => $tipoRecurso, 'isEdit' => false, 'submitLabel' => 'Guardar tipo de recurso'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

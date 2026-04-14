@extends('layouts.app')

@section('title', 'Editar tipo de recurso')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar tipo de recurso</h1>
            <p class="text-muted mb-0">Actualiza la información base del tipo de recurso.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-recurso.show', $tipoRecurso) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.tipos-recurso.update', $tipoRecurso) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-recurso._form', ['tipoRecurso' => $tipoRecurso, 'isEdit' => true, 'submitLabel' => 'Guardar cambios'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

@extends('layouts.app')

@section('title', 'Editar tipo de precio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar tipo de precio</h1>
            <p class="text-muted mb-0">Actualiza la información base del tipo de precio.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-precio.show', $tipoPrecio) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.tipos-precio.update', $tipoPrecio) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-precio._form', [
            'tipoPrecio' => $tipoPrecio,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
        ])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

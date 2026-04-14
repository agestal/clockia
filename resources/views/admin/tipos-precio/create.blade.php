@extends('layouts.app')

@section('title', 'Nuevo tipo de precio')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo tipo de precio</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de tipos de precio.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.tipos-precio.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.tipos-precio._form', [
            'tipoPrecio' => $tipoPrecio,
            'isEdit' => false,
            'submitLabel' => 'Guardar tipo de precio',
        ])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

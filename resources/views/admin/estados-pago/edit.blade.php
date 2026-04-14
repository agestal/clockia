@extends('layouts.app')

@section('title', 'Editar estado de pago')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar estado de pago</h1>
            <p class="text-muted mb-0">Actualiza la información base del estado de pago.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.estados-pago.show', $estadoPago) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.estados-pago.update', $estadoPago) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.estados-pago._form', ['estadoPago' => $estadoPago, 'isEdit' => true, 'submitLabel' => 'Guardar cambios'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

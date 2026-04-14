@extends('layouts.app')

@section('title', 'Nuevo estado de pago')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo estado de pago</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de estados de pago.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.estados-pago.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.estados-pago._form', ['estadoPago' => $estadoPago, 'isEdit' => false, 'submitLabel' => 'Guardar estado de pago'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

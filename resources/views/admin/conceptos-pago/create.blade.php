@extends('layouts.app')

@section('title', 'Nuevo concepto de pago')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo concepto de pago</h1>
        <p class="text-muted mb-0">Crea un nuevo elemento del catálogo de conceptos de pago.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')
    <form action="{{ route('admin.conceptos-pago.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.conceptos-pago._form', ['conceptoPago' => $conceptoPago, 'isEdit' => false, 'submitLabel' => 'Guardar concepto de pago'])
    </form>
@stop

@include('admin.catalogos-simple._form-scripts')

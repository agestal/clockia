@extends('layouts.app')

@section('title', 'Nueva combinación de recursos')

@section('content_header_extra')
    <h1 class="mb-1">Nueva combinación de recursos</h1>
    <p class="text-muted mb-0">Selecciona dos recursos que se puedan combinar entre sí.</p>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form method="POST" action="{{ route('admin.recurso-combinaciones.store') }}" novalidate>
        @include('admin.recurso-combinaciones._form', [
            'combinacion' => $combinacion,
            'isEdit' => false,
            'submitLabel' => 'Crear combinación',
        ])
    </form>
@stop

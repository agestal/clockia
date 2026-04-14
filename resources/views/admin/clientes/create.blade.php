@extends('layouts.app')

@section('title', 'Nuevo cliente')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nuevo cliente</h1>
        <p class="text-muted mb-0">Crea un nuevo cliente para el sistema de reservas.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.clientes.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.clientes._form', [
            'cliente' => $cliente,
            'isEdit' => false,
            'submitLabel' => 'Guardar cliente',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-basic-validation-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });
        });
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Nueva integración')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Nueva integración</h1>
        <p class="text-muted mb-0">Crea una nueva integración con un proveedor externo.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.integraciones.store') }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.integraciones._form', [
            'integracion' => $integracion,
            'isEdit' => false,
            'submitLabel' => 'Guardar integración',
        ])
    </form>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-basic-validation-form');

            form?.addEventListener('submit', () => {
                form.querySelectorAll('input[type="text"]').forEach((field) => {
                    field.value = field.value.replace(/\s+/g, ' ').trim();
                });
            });
        });
    </script>
@endpush

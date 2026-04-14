@extends('layouts.app')

@section('title', 'Editar disponibilidad')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar disponibilidad</h1>
            <p class="text-muted mb-0">Actualiza la franja de disponibilidad seleccionada.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.disponibilidades.show', $disponibilidad) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.disponibilidades.update', $disponibilidad) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.disponibilidades._form', [
            'disponibilidad' => $disponibilidad,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
            'recursos' => $recursos,
            'dayOptions' => $dayOptions,
            'horaInicioValue' => $horaInicioValue,
            'horaFinValue' => $horaFinValue,
        ])
    </form>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('.js-select2-recurso').select2({
                theme: 'bootstrap4',
                placeholder: 'Selecciona un recurso',
            });
        });
    </script>
@endpush

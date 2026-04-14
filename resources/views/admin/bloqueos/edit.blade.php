@extends('layouts.app')

@section('title', 'Editar bloqueo')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Editar bloqueo</h1>
            <p class="text-muted mb-0">Actualiza el bloqueo puntual seleccionado.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.bloqueos.show', $bloqueo) }}" class="btn btn-light border">Ver detalle</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <form action="{{ route('admin.bloqueos.update', $bloqueo) }}" method="POST" class="js-basic-validation-form" novalidate>
        @include('admin.bloqueos._form', [
            'bloqueo' => $bloqueo,
            'isEdit' => true,
            'submitLabel' => 'Guardar cambios',
            'recursos' => $recursos,
            'tiposBloqueo' => $tiposBloqueo,
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

            $('.js-select2-tipo-bloqueo').select2({
                theme: 'bootstrap4',
                placeholder: 'Selecciona un tipo de bloqueo',
            });

            const horaInicio = document.getElementById('hora_inicio');
            const horaFin = document.getElementById('hora_fin');
            const helper = document.querySelector('.js-time-helper');

            const refreshHelper = () => {
                if (!horaInicio.value && !horaFin.value) {
                    helper.textContent = 'Si dejas hora_inicio y hora_fin vacías, el bloqueo se considerará de día completo.';
                } else {
                    helper.textContent = 'Para un bloqueo por tramo horario debes informar ambas horas.';
                }
            };

            horaInicio.addEventListener('input', refreshHelper);
            horaFin.addEventListener('input', refreshHelper);
            refreshHelper();
        });
    </script>
@endpush

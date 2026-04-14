@extends('layouts.app')

@section('title', 'Detalle de la disponibilidad')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Detalle de la disponibilidad</h1>
            <p class="text-muted mb-0">Consulta la franja horaria configurada para el recurso.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.disponibilidades.edit', $disponibilidad) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $disponibilidad->id }}</dd>

                <dt class="col-sm-3">Recurso</dt>
                <dd class="col-sm-9">{{ $disponibilidad->recurso?->nombre ?: '—' }}</dd>

                <dt class="col-sm-3">Día de la semana</dt>
                <dd class="col-sm-9">{{ $dayOptions[$disponibilidad->dia_semana] ?? $disponibilidad->dia_semana }}</dd>

                <dt class="col-sm-3">Hora de inicio</dt>
                <dd class="col-sm-9">{{ substr((string) $disponibilidad->hora_inicio, 0, 5) }}</dd>

                <dt class="col-sm-3">Hora de fin</dt>
                <dd class="col-sm-9">{{ substr((string) $disponibilidad->hora_fin, 0, 5) }}</dd>

                <dt class="col-sm-3">Turno</dt>
                <dd class="col-sm-9">{{ $disponibilidad->nombre_turno ?: 'Sin nombre de turno' }}</dd>

                <dt class="col-sm-3">Buffer</dt>
                <dd class="col-sm-9">{{ $disponibilidad->buffer_minutos !== null ? $disponibilidad->buffer_minutos . ' minutos' : 'Sin buffer' }}</dd>

                <dt class="col-sm-3">Activa</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $disponibilidad->activo ? 'badge-success' : 'badge-secondary' }}">
                        {{ $disponibilidad->activo ? 'Sí' : 'No' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Creada</dt>
                <dd class="col-sm-9">{{ optional($disponibilidad->created_at)->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Actualizada</dt>
                <dd class="col-sm-9 mb-0">{{ optional($disponibilidad->updated_at)->format('d/m/Y H:i') }}</dd>
            </dl>
        </div>
    </div>
@stop

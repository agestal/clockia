@extends('layouts.app')

@section('title', 'Detalle del bloqueo')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Detalle del bloqueo</h1>
            <p class="text-muted mb-0">Consulta la información completa del bloqueo puntual.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.bloqueos.edit', $bloqueo) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $bloqueo->id }}</dd>

                <dt class="col-sm-3">Recurso</dt>
                <dd class="col-sm-9">{{ $bloqueo->recurso?->nombre ?: '— (bloqueo de negocio) —' }}</dd>

                <dt class="col-sm-3">Negocio</dt>
                <dd class="col-sm-9">{{ $bloqueo->negocio?->nombre ?: '—' }}</dd>

                <dt class="col-sm-3">Tipo de bloqueo</dt>
                <dd class="col-sm-9">{{ $bloqueo->tipoBloqueo?->nombre ?: '—' }}</dd>

                <dt class="col-sm-3">Modo</dt>
                <dd class="col-sm-9">
                    @if($bloqueo->es_recurrente)
                        <span class="badge badge-info">Recurrente</span>
                    @elseif($bloqueo->esRango())
                        <span class="badge badge-warning">Rango</span>
                    @else
                        <span class="badge badge-secondary">Puntual</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Fecha puntual</dt>
                <dd class="col-sm-9">{{ optional($bloqueo->fecha)->format('d/m/Y') ?: '—' }}</dd>

                <dt class="col-sm-3">Fecha inicio rango</dt>
                <dd class="col-sm-9">{{ optional($bloqueo->fecha_inicio)->format('d/m/Y') ?: '—' }}</dd>

                <dt class="col-sm-3">Fecha fin rango</dt>
                <dd class="col-sm-9">{{ optional($bloqueo->fecha_fin)->format('d/m/Y') ?: '—' }}</dd>

                <dt class="col-sm-3">Día de la semana</dt>
                <dd class="col-sm-9">{{ $bloqueo->dia_semana !== null ? ($dayOptions[$bloqueo->dia_semana] ?? $bloqueo->dia_semana) : '—' }}</dd>

                <dt class="col-sm-3">Hora de inicio</dt>
                <dd class="col-sm-9">{{ $horaInicioValue ?: '—' }}</dd>

                <dt class="col-sm-3">Hora de fin</dt>
                <dd class="col-sm-9">{{ $horaFinValue ?: '—' }}</dd>

                <dt class="col-sm-3">Día completo</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $bloqueo->esDiaCompleto() ? 'badge-warning' : 'badge-light border' }}">
                        {{ $bloqueo->esDiaCompleto() ? 'Sí' : 'No' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Activo</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $bloqueo->activo ? 'badge-success' : 'badge-secondary' }}">
                        {{ $bloqueo->activo ? 'Sí' : 'No' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Motivo</dt>
                <dd class="col-sm-9">{{ $bloqueo->motivo ?: 'Sin motivo' }}</dd>

                <dt class="col-sm-3">Creado</dt>
                <dd class="col-sm-9">{{ optional($bloqueo->created_at)->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Actualizado</dt>
                <dd class="col-sm-9 mb-0">{{ optional($bloqueo->updated_at)->format('d/m/Y H:i') }}</dd>
            </dl>
        </div>
    </div>
@stop

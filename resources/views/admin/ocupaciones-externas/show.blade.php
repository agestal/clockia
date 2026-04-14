@extends('layouts.app')

@section('title', 'Detalle de la ocupación externa')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Detalle de la ocupación externa</h1>
            <p class="text-muted mb-0">Consulta la información completa de la ocupación externa.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.ocupaciones-externas.edit', $ocupacion) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $ocupacion->id }}</dd>

                <dt class="col-sm-3">Negocio</dt>
                <dd class="col-sm-9">{{ $ocupacion->negocio?->nombre ?: '—' }}</dd>

                <dt class="col-sm-3">Recurso</dt>
                <dd class="col-sm-9">{{ $ocupacion->recurso?->nombre ?: '—' }}</dd>

                <dt class="col-sm-3">Integración</dt>
                <dd class="col-sm-9">{{ $ocupacion->integracion?->nombre ?: '—' }}</dd>

                <dt class="col-sm-3">Mapeo de integración</dt>
                <dd class="col-sm-9">{{ $ocupacion->integracionMapeo?->id ?: '—' }}</dd>

                <dt class="col-sm-3">Proveedor</dt>
                <dd class="col-sm-9">{{ $ocupacion->proveedor ?: '—' }}</dd>

                <dt class="col-sm-3">External ID</dt>
                <dd class="col-sm-9">{{ $ocupacion->external_id }}</dd>

                <dt class="col-sm-3">External Calendar ID</dt>
                <dd class="col-sm-9">{{ $ocupacion->external_calendar_id ?: '—' }}</dd>

                <dt class="col-sm-3">Título</dt>
                <dd class="col-sm-9">{{ $ocupacion->titulo ?: '—' }}</dd>

                <dt class="col-sm-3">Descripción</dt>
                <dd class="col-sm-9">{{ $ocupacion->descripcion ?: '—' }}</dd>

                <dt class="col-sm-3">Fecha</dt>
                <dd class="col-sm-9">{{ optional($ocupacion->fecha)->format('d/m/Y') ?: '—' }}</dd>

                <dt class="col-sm-3">Hora de inicio</dt>
                <dd class="col-sm-9">{{ $horaInicioValue ?: '—' }}</dd>

                <dt class="col-sm-3">Hora de fin</dt>
                <dd class="col-sm-9">{{ $horaFinValue ?: '—' }}</dd>

                <dt class="col-sm-3">Inicio datetime</dt>
                <dd class="col-sm-9">{{ optional($ocupacion->inicio_datetime)->format('d/m/Y H:i') ?: '—' }}</dd>

                <dt class="col-sm-3">Fin datetime</dt>
                <dd class="col-sm-9">{{ optional($ocupacion->fin_datetime)->format('d/m/Y H:i') ?: '—' }}</dd>

                <dt class="col-sm-3">Día completo</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $ocupacion->es_dia_completo ? 'badge-warning' : 'badge-light border' }}">
                        {{ $ocupacion->es_dia_completo ? 'Sí' : 'No' }}
                    </span>
                </dd>

                <dt class="col-sm-3">Origen</dt>
                <dd class="col-sm-9">{{ $ocupacion->origen ?: '—' }}</dd>

                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">{{ $ocupacion->estado ?: '—' }}</dd>

                <dt class="col-sm-3">Último sync</dt>
                <dd class="col-sm-9">{{ optional($ocupacion->ultimo_sync_at)->format('d/m/Y H:i') ?: '—' }}</dd>

                <dt class="col-sm-3">Creado</dt>
                <dd class="col-sm-9">{{ optional($ocupacion->created_at)->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Actualizado</dt>
                <dd class="col-sm-9 mb-0">{{ optional($ocupacion->updated_at)->format('d/m/Y H:i') }}</dd>
            </dl>
        </div>
    </div>
@stop

@extends('layouts.app')

@section('title', 'Detalle del mapeo de integración')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $mapeo->nombre_externo ?: $mapeo->external_id }}</h1>
            <p class="text-muted mb-0">Detalle del mapeo de integración y sus relaciones.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.integracion-mapeos.edit', $mapeo) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Datos generales</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID</dt>
                        <dd class="col-sm-7">{{ $mapeo->id }}</dd>
                        <dt class="col-sm-5">Integración</dt>
                        <dd class="col-sm-7">{{ $mapeo->integracion?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Tipo origen</dt>
                        <dd class="col-sm-7">{{ $mapeo->tipo_origen }}</dd>
                        <dt class="col-sm-5">External ID</dt>
                        <dd class="col-sm-7">{{ $mapeo->external_id }}</dd>
                        <dt class="col-sm-5">External Parent ID</dt>
                        <dd class="col-sm-7">{{ $mapeo->external_parent_id ?: '—' }}</dd>
                        <dt class="col-sm-5">Nombre externo</dt>
                        <dd class="col-sm-7">{{ $mapeo->nombre_externo ?: '—' }}</dd>
                        <dt class="col-sm-5">Activo</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $mapeo->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $mapeo->activo ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($mapeo->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($mapeo->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Destino interno</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Negocio</dt>
                        <dd class="col-sm-7">{{ $mapeo->negocio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Recurso</dt>
                        <dd class="col-sm-7">{{ $mapeo->recurso?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Servicio</dt>
                        <dd class="col-sm-7">{{ $mapeo->servicio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Ocupaciones externas</dt>
                        <dd class="col-sm-7 mb-0">
                            <span class="badge badge-light border">{{ $mapeo->ocupaciones_externas_count }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            @if($mapeo->configuracion)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">Configuración</h3>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0"><code>{{ json_encode($mapeo->configuracion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

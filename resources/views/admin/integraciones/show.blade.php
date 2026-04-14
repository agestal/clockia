@extends('layouts.app')

@section('title', 'Detalle de la integración')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $integracion->nombre }}</h1>
            <p class="text-muted mb-0">Detalle de la integración y resumen de su uso en el sistema.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.integraciones.edit', $integracion) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h3 class="card-title mb-0">Datos generales</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID</dt>
                        <dd class="col-sm-7">{{ $integracion->id }}</dd>
                        <dt class="col-sm-5">Nombre</dt>
                        <dd class="col-sm-7">{{ $integracion->nombre }}</dd>
                        <dt class="col-sm-5">Negocio</dt>
                        <dd class="col-sm-7">{{ $integracion->negocio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Proveedor</dt>
                        <dd class="col-sm-7">
                            @switch($integracion->proveedor)
                                @case('google_calendar') Google Calendar @break
                                @default {{ $integracion->proveedor }}
                            @endswitch
                        </dd>
                        <dt class="col-sm-5">Modo operación</dt>
                        <dd class="col-sm-7">
                            @switch($integracion->modo_operacion)
                                @case('solo_clockia') Solo Clockia @break
                                @case('coexistencia') Coexistencia @break
                                @case('migracion') Migración @break
                                @default {{ $integracion->modo_operacion }}
                            @endswitch
                        </dd>
                        <dt class="col-sm-5">Estado</dt>
                        <dd class="col-sm-7">
                            @switch($integracion->estado)
                                @case('pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                    @break
                                @case('conectada')
                                    <span class="badge badge-success">Conectada</span>
                                    @break
                                @case('error')
                                    <span class="badge badge-danger">Error</span>
                                    @break
                                @case('desactivada')
                                    <span class="badge badge-secondary">Desactivada</span>
                                    @break
                                @default
                                    <span class="badge badge-light border">{{ $integracion->estado }}</span>
                            @endswitch
                        </dd>
                        <dt class="col-sm-5">Activo</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $integracion->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $integracion->activo ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        @if($integracion->ultimo_sync_at)
                            <dt class="col-sm-5">Última sync</dt>
                            <dd class="col-sm-7">{{ $integracion->ultimo_sync_at->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($integracion->ultimo_error)
                            <dt class="col-sm-5">Último error</dt>
                            <dd class="col-sm-7"><span class="text-danger">{{ $integracion->ultimo_error }}</span></dd>
                        @endif
                        @if($integracion->configuracion)
                            <dt class="col-sm-5">Configuración</dt>
                            <dd class="col-sm-7"><pre class="mb-0 small bg-light p-2 rounded">{{ json_encode($integracion->configuracion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></dd>
                        @endif
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($integracion->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($integracion->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $integracion->cuentas_count }}</h3>
                            <p>Cuentas</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-circle text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $integracion->mapeos_count }}</h3>
                            <p>Mapeos</p>
                        </div>
                        <div class="icon"><i class="fas fa-exchange-alt text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $integracion->ocupaciones_externas_count }}</h3>
                            <p>Ocup. externas</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-alt text-muted"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

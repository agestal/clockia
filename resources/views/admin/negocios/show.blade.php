@extends('layouts.app')

@section('title', 'Detalle del negocio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $negocio->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del negocio y resumen de su actividad en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.negocios.edit', $negocio) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Datos generales</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID</dt>
                        <dd class="col-sm-7">{{ $negocio->id }}</dd>
                        <dt class="col-sm-5">Nombre</dt>
                        <dd class="col-sm-7">{{ $negocio->nombre }}</dd>
                        <dt class="col-sm-5">Tipo</dt>
                        <dd class="col-sm-7">{{ $negocio->tipoNegocio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Email</dt>
                        <dd class="col-sm-7">{{ $negocio->email ?: 'Sin email' }}</dd>
                        <dt class="col-sm-5">Teléfono</dt>
                        <dd class="col-sm-7">{{ $negocio->telefono ?: 'Sin teléfono' }}</dd>
                        <dt class="col-sm-5">Zona horaria</dt>
                        <dd class="col-sm-7">{{ $negocio->zona_horaria }}</dd>
                        <dt class="col-sm-5">Activo</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $negocio->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $negocio->activo ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Dirección</dt>
                        <dd class="col-sm-7">{{ $negocio->direccion ?: 'Sin dirección' }}</dd>
                        <dt class="col-sm-5">URL pública</dt>
                        <dd class="col-sm-7">
                            @if($negocio->url_publica)
                                <a href="{{ $negocio->url_publica }}" target="_blank" rel="noopener">{{ $negocio->url_publica }}</a>
                            @else
                                Sin URL
                            @endif
                        </dd>
                        <dt class="col-sm-5">Horas cancel.</dt>
                        <dd class="col-sm-7">{{ $negocio->horas_minimas_cancelacion !== null ? $negocio->horas_minimas_cancelacion . 'h' : 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Modificación</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $negocio->permite_modificacion ? 'badge-success' : 'badge-secondary' }}">
                                {{ $negocio->permite_modificacion ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Máx. combinables</dt>
                        <dd class="col-sm-7">{{ $negocio->maxRecursosCombinablesEfectivo() }}</dd>
                        <dt class="col-sm-5">Personalidad chat</dt>
                        <dd class="col-sm-7">{{ $negocio->chat_personality ?: 'Default del sistema' }}</dd>
                        <dt class="col-sm-5">Reglas sistema</dt>
                        <dd class="col-sm-7">{{ $negocio->chat_system_rules ? 'Configuradas' : 'Sin reglas' }}</dd>
                        <dt class="col-sm-5">Campos chat</dt>
                        <dd class="col-sm-7">{{ $negocio->chat_required_fields ? 'Personalizado' : 'Defaults del sistema' }}</dd>
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($negocio->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($negocio->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $negocio->servicios_count }}</h3>
                            <p>Servicios</p>
                        </div>
                        <div class="icon"><i class="fas fa-concierge-bell text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $negocio->recursos_count }}</h3>
                            <p>Recursos</p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $negocio->reservas_count }}</h3>
                            <p>Reservas</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-check text-muted"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h3 class="card-title mb-0">Servicios</h3></div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($servicios as $servicio)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $servicio->nombre }}</span>
                                        <span class="badge {{ $servicio->activo ? 'badge-success' : 'badge-secondary' }}">{{ $servicio->activo ? 'Sí' : 'No' }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Sin servicios relacionados.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h3 class="card-title mb-0">Recursos</h3></div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($recursos as $recurso)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $recurso->nombre }}</span>
                                        <span class="badge {{ $recurso->activo ? 'badge-success' : 'badge-secondary' }}">{{ $recurso->activo ? 'Sí' : 'No' }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Sin recursos relacionados.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h3 class="card-title mb-0">Reservas</h3></div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($reservas as $reserva)
                                    <li class="list-group-item">
                                        <div>{{ optional($reserva->fecha)->format('d/m/Y') }} {{ $reserva->hora_inicio }}</div>
                                        <div class="small text-muted">{{ $reserva->cliente?->nombre ?: '—' }} · {{ $reserva->estadoReserva?->nombre ?: '—' }}</div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Sin reservas relacionadas.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if($negocio->descripcion_publica)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Descripción pública</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $negocio->descripcion_publica }}</p></div>
                </div>
            @endif

            @if($negocio->politica_cancelacion)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Política de cancelación</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $negocio->politica_cancelacion }}</p></div>
                </div>
            @endif
        </div>
    </div>
@stop

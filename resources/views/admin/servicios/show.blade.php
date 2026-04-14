@extends('layouts.app')

@section('title', 'Detalle del servicio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $servicio->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del servicio, recursos asociados y resumen de reservas.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.servicios.edit', $servicio) }}" class="btn btn-primary">Editar</a>
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
                        <dd class="col-sm-7">{{ $servicio->id }}</dd>
                        <dt class="col-sm-5">Nombre</dt>
                        <dd class="col-sm-7">{{ $servicio->nombre }}</dd>
                        <dt class="col-sm-5">Negocio</dt>
                        <dd class="col-sm-7">{{ $servicio->negocio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Tipo de precio</dt>
                        <dd class="col-sm-7">{{ $servicio->tipoPrecio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Duración</dt>
                        <dd class="col-sm-7">{{ $servicio->duracion_minutos }} min</dd>
                        <dt class="col-sm-5">Precio base</dt>
                        <dd class="col-sm-7">{{ number_format((float) $servicio->precio_base, 2, ',', '.') }}</dd>
                        <dt class="col-sm-5">Requiere pago</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $servicio->requiere_pago ? 'badge-success' : 'badge-secondary' }}">
                                {{ $servicio->requiere_pago ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Activo</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $servicio->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $servicio->activo ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Reembolsable</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $servicio->es_reembolsable ? 'badge-success' : 'badge-secondary' }}">
                                {{ $servicio->es_reembolsable ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Precio/tiempo</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $servicio->precio_por_unidad_tiempo ? 'badge-info' : 'badge-secondary' }}">
                                {{ $servicio->precio_por_unidad_tiempo ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Señal</dt>
                        <dd class="col-sm-7">{{ $servicio->porcentaje_senal !== null ? number_format((float) $servicio->porcentaje_senal, 2, ',', '.') . '%' : 'Sin señal' }}</dd>
                        <dt class="col-sm-5">Horas cancel.</dt>
                        <dd class="col-sm-7">{{ $servicio->horas_minimas_cancelacion !== null ? $servicio->horas_minimas_cancelacion . 'h' : 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($servicio->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($servicio->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-6">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $servicio->recursos->count() }}</h3>
                            <p>Recursos asociados</p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $servicio->reservas->count() }}</h3>
                            <p>Reservas</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-check text-muted"></i></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Recursos asociados</h3>
                </div>
                <div class="card-body">
                    @if($servicio->recursos->isEmpty())
                        <p class="text-muted mb-0">Este servicio no tiene recursos asociados.</p>
                    @else
                        <div class="d-flex flex-wrap">
                            @foreach($servicio->recursos as $recurso)
                                <span class="badge badge-light border mr-2 mb-2 px-3 py-2">{{ $recurso->nombre }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Reservas recientes</h3>
                </div>
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

            @if($servicio->descripcion)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">Descripción</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $servicio->descripcion }}</p>
                    </div>
                </div>
            @endif

            @if($servicio->notas_publicas)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Notas públicas</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $servicio->notas_publicas }}</p></div>
                </div>
            @endif

            @if($servicio->instrucciones_previas)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Instrucciones previas</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $servicio->instrucciones_previas }}</p></div>
                </div>
            @endif

            @if($servicio->documentacion_requerida)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Documentación requerida</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $servicio->documentacion_requerida }}</p></div>
                </div>
            @endif
        </div>
    </div>
@stop

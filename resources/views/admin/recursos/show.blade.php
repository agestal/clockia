@extends('layouts.app')

@section('title', 'Detalle del recurso')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $recurso->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del recurso y resumen de su uso en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.recursos.edit', $recurso) }}" class="btn btn-primary">Editar</a>
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
                        <dd class="col-sm-7">{{ $recurso->id }}</dd>
                        <dt class="col-sm-5">Nombre</dt>
                        <dd class="col-sm-7">{{ $recurso->nombre }}</dd>
                        <dt class="col-sm-5">Negocio</dt>
                        <dd class="col-sm-7">{{ $recurso->negocio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Tipo</dt>
                        <dd class="col-sm-7">{{ $recurso->tipoRecurso?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Capacidad</dt>
                        <dd class="col-sm-7">{{ $recurso->capacidad ?: 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Cap. mínima</dt>
                        <dd class="col-sm-7">{{ $recurso->capacidad_minima ?: 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Combinable</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $recurso->combinable ? 'badge-success' : 'badge-secondary' }}">
                                {{ $recurso->combinable ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Activo</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $recurso->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $recurso->activo ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($recurso->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($recurso->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $recurso->disponibilidades->count() }}</h3>
                            <p>Disponibilidades</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $recurso->bloqueos->count() }}</h3>
                            <p>Bloqueos</p>
                        </div>
                        <div class="icon"><i class="fas fa-ban text-muted"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3>{{ $recurso->reservas->count() }}</h3>
                            <p>Reservas</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-check text-muted"></i></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Servicios asociados</h3>
                </div>
                <div class="card-body">
                    @if($recurso->servicios->isEmpty())
                        <p class="text-muted mb-0">Este recurso no tiene servicios asociados.</p>
                    @else
                        <div class="d-flex flex-wrap">
                            @foreach($recurso->servicios as $servicio)
                                <span class="badge badge-light border mr-2 mb-2 px-3 py-2">{{ $servicio->nombre }}</span>
                            @endforeach
                        </div>
                    @endif

                    <p class="small text-muted mb-0 mt-2">La relación con servicios se visualiza aquí, pero su gestión se realiza desde el CRUD de Servicio.</p>
                </div>
            </div>

            @if($recurso->combinable && $recurso->recursosCombinables->isNotEmpty())
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Recursos combinables</h3></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            @foreach($recurso->recursosCombinables as $combinacion)
                                <span class="badge badge-light border mr-2 mb-2 px-3 py-2">{{ $combinacion->recursoCombinado->nombre }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($recurso->notas_publicas)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Notas públicas</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $recurso->notas_publicas }}</p></div>
                </div>
            @endif
        </div>
    </div>
@stop

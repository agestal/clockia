@extends('layouts.app')

@section('title', 'Detalle del pago')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Pago #{{ $pago->id }}</h1>
            <p class="text-muted mb-0">Detalle del pago registrado en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.pagos.edit', $pago) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Datos del pago</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID</dt>
                        <dd class="col-sm-7">{{ $pago->id }}</dd>
                        <dt class="col-sm-5">Tipo de pago</dt>
                        <dd class="col-sm-7">{{ $pago->tipoPago?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Concepto</dt>
                        <dd class="col-sm-7">{{ $pago->conceptoPago?->nombre ?: 'Sin concepto' }}</dd>
                        <dt class="col-sm-5">Estado</dt>
                        <dd class="col-sm-7">{{ $pago->estadoPago?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Importe</dt>
                        <dd class="col-sm-7">{{ number_format((float) $pago->importe, 2, ',', '.') }}</dd>
                        <dt class="col-sm-5">Referencia</dt>
                        <dd class="col-sm-7">{{ $pago->referencia_externa ?: 'Sin referencia' }}</dd>
                        <dt class="col-sm-5">Fecha de pago</dt>
                        <dd class="col-sm-7">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'Sin registrar' }}</dd>
                        <dt class="col-sm-5">Enlace de pago</dt>
                        <dd class="col-sm-7">
                            @if($pago->enlace_pago_externo)
                                <a href="{{ $pago->enlace_pago_externo }}" target="_blank" rel="noopener">{{ Str::limit($pago->enlace_pago_externo, 50) }}</a>
                            @else
                                Sin enlace
                            @endif
                        </dd>
                        <dt class="col-sm-5">Iniciado por bot</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $pago->iniciado_por_bot ? 'badge-info' : 'badge-secondary' }}">
                                {{ $pago->iniciado_por_bot ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($pago->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($pago->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Reserva asociada</h3>
                </div>
                <div class="card-body">
                    @if($pago->reserva)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Reserva</dt>
                            <dd class="col-sm-8">#{{ $pago->reserva->id }}</dd>
                            <dt class="col-sm-4">Fecha</dt>
                            <dd class="col-sm-8">{{ optional($pago->reserva->fecha)->format('d/m/Y') ?: '—' }}</dd>
                            <dt class="col-sm-4">Hora inicio</dt>
                            <dd class="col-sm-8">{{ $pago->reserva->hora_inicio ? substr((string) $pago->reserva->hora_inicio, 0, 5) : '—' }}</dd>
                            <dt class="col-sm-4">Hora fin</dt>
                            <dd class="col-sm-8 mb-0">{{ $pago->reserva->hora_fin ? substr((string) $pago->reserva->hora_fin, 0, 5) : '—' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">No hay reserva asociada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@extends('layouts.app')

@section('title', 'Detalle de la reserva')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">Reserva #{{ $reserva->id }}</h1>
            <p class="text-muted mb-0">Detalle completo de la reserva y sus pagos asociados.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.reservas.edit', $reserva) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Datos de la reserva</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Negocio</dt>
                        <dd class="col-sm-7">{{ $reserva->negocio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Localizador</dt>
                        <dd class="col-sm-7"><code>{{ $reserva->localizador ?: '—' }}</code></dd>
                        <dt class="col-sm-5">Servicio</dt>
                        <dd class="col-sm-7">{{ $reserva->servicio?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Recurso</dt>
                        <dd class="col-sm-7">{{ $reserva->recurso?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Cliente</dt>
                        <dd class="col-sm-7">{{ $reserva->cliente?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Fecha</dt>
                        <dd class="col-sm-7">{{ optional($reserva->fecha)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5">Hora inicio</dt>
                        <dd class="col-sm-7">{{ substr((string) $reserva->hora_inicio, 0, 5) }}</dd>
                        <dt class="col-sm-5">Hora fin</dt>
                        <dd class="col-sm-7">{{ substr((string) $reserva->hora_fin, 0, 5) }}</dd>
                        <dt class="col-sm-5">Inicio datetime</dt>
                        <dd class="col-sm-7">{{ $reserva->inicio_datetime ? $reserva->inicio_datetime->format('d/m/Y H:i') : '—' }}</dd>
                        <dt class="col-sm-5">Fin datetime</dt>
                        <dd class="col-sm-7">{{ $reserva->fin_datetime ? $reserva->fin_datetime->format('d/m/Y H:i') : '—' }}</dd>
                        <dt class="col-sm-5">N. personas</dt>
                        <dd class="col-sm-7">{{ $reserva->numero_personas ?: 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Precio calculado</dt>
                        <dd class="col-sm-7">{{ number_format((float) $reserva->precio_calculado, 2, ',', '.') }}</dd>
                        <dt class="col-sm-5">Precio total</dt>
                        <dd class="col-sm-7">{{ $reserva->precio_total !== null ? number_format((float) $reserva->precio_total, 2, ',', '.') : 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Precio final</dt>
                        <dd class="col-sm-7">{{ number_format((float) $reserva->precio_final, 2, ',', '.') }}</dd>
                        <dt class="col-sm-5">Estado</dt>
                        <dd class="col-sm-7">{{ $reserva->estadoReserva?->nombre ?: '—' }}</dd>
                        <dt class="col-sm-5">Fin estimado</dt>
                        <dd class="col-sm-7">{{ $reserva->fecha_estimada_fin ? $reserva->fecha_estimada_fin->format('d/m/Y H:i') : 'Sin definir' }}</dd>
                        <dt class="col-sm-5">Origen</dt>
                        <dd class="col-sm-7">{{ $reserva->origen_reserva ?: 'clockia' }}</dd>
                        <dt class="col-sm-5">Importada</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $reserva->importada_externamente ? 'badge-info' : 'badge-secondary' }}">
                                {{ $reserva->importada_externamente ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Doc. entregada</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $reserva->documentacion_entregada ? 'badge-success' : 'badge-secondary' }}">
                                {{ $reserva->documentacion_entregada ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Creado</dt>
                        <dd class="col-sm-7">{{ optional($reserva->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-5">Actualizado</dt>
                        <dd class="col-sm-7 mb-0">{{ optional($reserva->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            @if($reserva->notas)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">Notas</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $reserva->notas }}</p>
                    </div>
                </div>
            @endif

            @if($reserva->instrucciones_llegada)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Instrucciones de llegada</h3></div>
                    <div class="card-body"><p class="mb-0 text-muted">{{ $reserva->instrucciones_llegada }}</p></div>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h3 class="card-title mb-0">Política efectiva</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Horas mín. cancelación</dt>
                        <dd class="col-sm-7">{{ $policy['horas_minimas_cancelacion'] }}h</dd>
                        <dt class="col-sm-5">Permite modificación</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $policy['permite_modificacion'] ? 'badge-success' : 'badge-secondary' }}">
                                {{ $policy['permite_modificacion'] ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Es reembolsable</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $policy['es_reembolsable'] ? 'badge-success' : 'badge-secondary' }}">
                                {{ $policy['es_reembolsable'] ? 'Sí' : 'No' }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Porcentaje señal</dt>
                        <dd class="col-sm-7 mb-0">{{ $policy['porcentaje_senal'] !== null ? number_format((float) $policy['porcentaje_senal'], 2, ',', '.') . '%' : 'Sin señal' }}</dd>
                    </dl>
                    <small class="text-muted d-block mt-2">Valores efectivos tras aplicar override → servicio → negocio → fallback.</small>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h3 class="card-title mb-0">Recursos de la reserva</h3></div>
                <div class="card-body p-0">
                    @if($reserva->reservaRecursos->isEmpty())
                        <p class="text-muted m-3 mb-0">Sin líneas de recursos asociadas.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($reserva->reservaRecursos as $linea)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $linea->recurso?->nombre ?: '—' }}</strong>
                                        <span class="text-muted">{{ optional($linea->fecha)->format('d/m/Y') }} · {{ substr((string) $linea->hora_inicio, 0, 5) }} - {{ substr((string) $linea->hora_fin, 0, 5) }}</span>
                                    </div>
                                    @if($linea->notas)
                                        <div class="small text-muted mt-1">{{ $linea->notas }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            @if($reserva->fecha_cancelacion || $reserva->motivo_cancelacion || $reserva->cancelada_por)
                <div class="card shadow-sm border-0 border-left border-danger" style="border-left-width: 4px !important;">
                    <div class="card-header bg-white"><h3 class="card-title mb-0 text-danger">Datos de cancelación</h3></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Fecha</dt>
                            <dd class="col-sm-8">{{ $reserva->fecha_cancelacion ? $reserva->fecha_cancelacion->format('d/m/Y H:i') : '—' }}</dd>
                            <dt class="col-sm-4">Cancelada por</dt>
                            <dd class="col-sm-8">{{ $reserva->cancelada_por ?: '—' }}</dd>
                            <dt class="col-sm-4">Motivo</dt>
                            <dd class="col-sm-8 mb-0">{{ $reserva->motivo_cancelacion ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title mb-0">Pagos relacionados</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($reserva->pagos as $pago)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>#{{ $pago->id }} · {{ $pago->tipoPago?->nombre ?: '—' }}</span>
                                    <strong>{{ number_format((float) $pago->importe, 2, ',', '.') }}</strong>
                                </div>
                                <div class="small text-muted">
                                    {{ $pago->estadoPago?->nombre ?: '—' }}
                                    @if($pago->fecha_pago)
                                        · {{ $pago->fecha_pago->format('d/m/Y H:i') }}
                                    @endif
                                    @if($pago->referencia_externa)
                                        · {{ $pago->referencia_externa }}
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Sin pagos relacionados.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            @if($reserva->reservaIntegraciones->isNotEmpty())
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><h3 class="card-title mb-0">Vínculos externos</h3></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($reserva->reservaIntegraciones as $vinculo)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $vinculo->proveedor }}</strong>
                                        <span class="badge badge-light border">{{ $vinculo->estado_sync ?: 'sin estado' }}</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        External ID: <code>{{ $vinculo->external_id }}</code>
                                        @if($vinculo->direccion_sync)
                                            · Sync: {{ $vinculo->direccion_sync }}
                                        @endif
                                        @if($vinculo->ultimo_sync_at)
                                            · Último sync: {{ $vinculo->ultimo_sync_at->format('d/m/Y H:i') }}
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

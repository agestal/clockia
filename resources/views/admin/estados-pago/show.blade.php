@extends('layouts.app')

@section('title', 'Detalle del estado de pago')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $estadoPago->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del estado de pago y resumen de su uso en el sistema.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.estados-pago.edit', $estadoPago) }}" class="btn btn-primary">Editar</a>
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
                        <dt class="col-sm-4">ID</dt><dd class="col-sm-8">{{ $estadoPago->id }}</dd>
                        <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8">{{ $estadoPago->nombre }}</dd>
                        <dt class="col-sm-4">Descripción</dt><dd class="col-sm-8">{{ $estadoPago->descripcion ?: 'Sin descripción' }}</dd>
                        <dt class="col-sm-4">Pagos</dt><dd class="col-sm-8">{{ $estadoPago->pagos_count }}</dd>
                        <dt class="col-sm-4">Creado</dt><dd class="col-sm-8">{{ optional($estadoPago->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Actualizado</dt><dd class="col-sm-8 mb-0">{{ optional($estadoPago->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Pagos relacionados</h3>
                    <span class="badge badge-light border">{{ $estadoPago->pagos_count }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-right">Importe</th>
                                    <th>Referencia externa</th>
                                    <th>Fecha de pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagos as $pago)
                                    <tr>
                                        <td class="text-right">{{ number_format((float) $pago->importe, 2, ',', '.') }}</td>
                                        <td>{{ $pago->referencia_externa ?: 'Sin referencia' }}</td>
                                        <td>{{ optional($pago->fecha_pago)->format('d/m/Y H:i') ?: 'Sin fecha' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No hay pagos relacionados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($estadoPago->pagos_count > $pagos->count())
                    <div class="card-footer bg-white text-muted">Se muestran los primeros {{ $pagos->count() }} pagos relacionados.</div>
                @endif
            </div>
        </div>
    </div>
@stop

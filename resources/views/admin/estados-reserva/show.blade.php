@extends('layouts.app')

@section('title', 'Detalle del estado de reserva')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $estadoReserva->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del estado de reserva y resumen de su uso en el sistema.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.estados-reserva.edit', $estadoReserva) }}" class="btn btn-primary">Editar</a>
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
                        <dt class="col-sm-4">ID</dt><dd class="col-sm-8">{{ $estadoReserva->id }}</dd>
                        <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8">{{ $estadoReserva->nombre }}</dd>
                        <dt class="col-sm-4">Descripción</dt><dd class="col-sm-8">{{ $estadoReserva->descripcion ?: 'Sin descripción' }}</dd>
                        <dt class="col-sm-4">Reservas</dt><dd class="col-sm-8">{{ $estadoReserva->reservas_count }}</dd>
                        <dt class="col-sm-4">Creado</dt><dd class="col-sm-8">{{ optional($estadoReserva->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Actualizado</dt><dd class="col-sm-8 mb-0">{{ optional($estadoReserva->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Reservas relacionadas</h3>
                    <span class="badge badge-light border">{{ $estadoReserva->reservas_count }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora inicio</th>
                                    <th>Hora fin</th>
                                    <th class="text-right">Precio final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservas as $reserva)
                                    <tr>
                                        <td>{{ optional($reserva->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $reserva->hora_inicio }}</td>
                                        <td>{{ $reserva->hora_fin }}</td>
                                        <td class="text-right">{{ number_format((float) ($reserva->precio_total ?? $reserva->precio_calculado), 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No hay reservas relacionadas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($estadoReserva->reservas_count > $reservas->count())
                    <div class="card-footer bg-white text-muted">Se muestran las primeras {{ $reservas->count() }} reservas relacionadas.</div>
                @endif
            </div>
        </div>
    </div>
@stop

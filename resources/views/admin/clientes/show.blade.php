@extends('layouts.app')

@section('title', 'Detalle del cliente')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $cliente->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del cliente y resumen de sus reservas.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-primary">Editar</a>
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
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $cliente->id }}</dd>
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8">{{ $cliente->nombre }}</dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $cliente->email ?: 'Sin email' }}</dd>
                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">{{ $cliente->telefono ?: 'Sin teléfono' }}</dd>
                        <dt class="col-sm-4">Reservas</dt>
                        <dd class="col-sm-8">{{ $cliente->reservas_count }}</dd>
                        <dt class="col-sm-4">Notas</dt>
                        <dd class="col-sm-8">{{ $cliente->notas ?: 'Sin notas' }}</dd>
                        <dt class="col-sm-4">Creado</dt>
                        <dd class="col-sm-8">{{ optional($cliente->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Actualizado</dt>
                        <dd class="col-sm-8 mb-0">{{ optional($cliente->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Reservas relacionadas</h3>
                    <span class="badge badge-light border">{{ $cliente->reservas_count }}</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Negocio</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th class="text-right">Precio final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservas as $reserva)
                                    <tr>
                                        <td>{{ optional($reserva->fecha)->format('d/m/Y') }} {{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</td>
                                        <td>{{ $reserva->negocio?->nombre ?: '—' }}</td>
                                        <td>{{ $reserva->servicio?->nombre ?: '—' }}</td>
                                        <td>{{ $reserva->estadoReserva?->nombre ?: '—' }}</td>
                                        <td class="text-right">{{ number_format((float) ($reserva->precio_total ?? $reserva->precio_calculado), 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay reservas relacionadas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($cliente->reservas_count > $reservas->count())
                    <div class="card-footer bg-white text-muted">
                        Se muestran las primeras {{ $reservas->count() }} reservas relacionadas.
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

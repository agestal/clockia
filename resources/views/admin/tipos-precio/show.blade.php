@extends('layouts.app')

@section('title', 'Detalle del tipo de precio')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <h1 class="mb-1">{{ $tipoPrecio->nombre }}</h1>
            <p class="text-muted mb-0">Detalle del tipo de precio y resumen de su uso en el sistema.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.tipos-precio.edit', $tipoPrecio) }}" class="btn btn-primary">Editar</a>
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
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">{{ $tipoPrecio->id }}</dd>
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8">{{ $tipoPrecio->nombre }}</dd>
                        <dt class="col-sm-4">Descripción</dt>
                        <dd class="col-sm-8">{{ $tipoPrecio->descripcion ?: 'Sin descripción' }}</dd>
                        <dt class="col-sm-4">Servicios</dt>
                        <dd class="col-sm-8">{{ $tipoPrecio->servicios_count }}</dd>
                        <dt class="col-sm-4">Creado</dt>
                        <dd class="col-sm-8">{{ optional($tipoPrecio->created_at)->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Actualizado</dt>
                        <dd class="col-sm-8 mb-0">{{ optional($tipoPrecio->updated_at)->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Servicios relacionados</h3>
                    <span class="badge badge-light border">{{ $tipoPrecio->servicios_count }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-right">Precio base</th>
                                    <th class="text-center">Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicios as $servicio)
                                    <tr>
                                        <td>{{ $servicio->nombre }}</td>
                                        <td class="text-right">{{ number_format((float) $servicio->precio_base, 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $servicio->activo ? 'badge-success' : 'badge-secondary' }}">{{ $servicio->activo ? 'Sí' : 'No' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No hay servicios relacionados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($tipoPrecio->servicios_count > $servicios->count())
                    <div class="card-footer bg-white text-muted">Se muestran los primeros {{ $servicios->count() }} servicios relacionados.</div>
                @endif
            </div>
        </div>
    </div>
@stop

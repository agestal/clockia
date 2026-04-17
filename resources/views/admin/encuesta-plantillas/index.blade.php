@extends('layouts.app')

@section('title', 'Encuestas')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Encuestas</h1>
        <p class="text-muted mb-0">Define las preguntas de puntuacion que se enviaran por email despues de cada experiencia.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
        <form method="GET" action="{{ route('admin.encuesta-plantillas.index') }}" class="form-inline">
            <label for="negocio_id" class="mr-2">Negocio</label>
            <select id="negocio_id" name="negocio_id" class="form-control mr-2">
                <option value="">Todos</option>
                @foreach($negocios as $negocio)
                    <option value="{{ $negocio->id }}" @selected($selectedBusinessId === $negocio->id)>{{ $negocio->nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </form>

        <a href="{{ route('admin.encuesta-plantillas.create', ['negocio_id' => $selectedBusinessId ?: null]) }}" class="btn btn-primary mt-2 mt-md-0">Nueva encuesta</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Nombre</th>
                            <th>Preguntas activas</th>
                            <th>Escala</th>
                            <th>Estado</th>
                            <th>Envios</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plantillas as $plantilla)
                            <tr>
                                <td class="align-middle">{{ $plantilla->negocio?->nombre }}</td>
                                <td class="align-middle">
                                    <div class="font-weight-semibold">{{ $plantilla->nombre }}</div>
                                    @if($plantilla->predeterminada)
                                        <span class="badge badge-success">Predeterminada</span>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $plantilla->preguntas_activas_count }}</td>
                                <td class="align-middle">{{ $plantilla->escala_min }} - {{ $plantilla->escala_max }}</td>
                                <td class="align-middle">
                                    <span class="badge {{ $plantilla->activo ? 'badge-primary' : 'badge-secondary' }}">
                                        {{ $plantilla->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="align-middle">{{ $plantilla->encuestas_count }}</td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.encuesta-plantillas.edit', $plantilla) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('admin.encuesta-plantillas.destroy', $plantilla) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que quieres eliminar esta encuesta?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No hay encuestas disponibles para los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

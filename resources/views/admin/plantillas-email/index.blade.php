@extends('layouts.app')

@section('title', 'Plantillas de email')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Plantillas de email</h1>
        <p class="text-muted mb-0">Confirma, recuerda y pide feedback con textos y colores propios de cada negocio.</p>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.plantillas-email.index') }}" class="form-row align-items-end">
                <div class="form-group col-lg-4 mb-lg-0">
                    <label for="negocio_id" class="form-label">Negocio</label>
                    <select id="negocio_id" name="negocio_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($negocios as $negocio)
                            <option value="{{ $negocio->id }}" @selected($selectedBusinessId === $negocio->id)>{{ $negocio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-3 mb-lg-0">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Tipo</th>
                            <th>Asunto</th>
                            <th>Ultima actualizacion</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plantillas as $plantilla)
                            @php($preview = $plantilla->resolved())
                            <tr>
                                <td class="align-middle">{{ $plantilla->negocio?->nombre }}</td>
                                <td class="align-middle">{{ $plantilla->etiquetaTipo() }}</td>
                                <td class="align-middle text-muted">{{ $preview['asunto'] }}</td>
                                <td class="align-middle text-muted">{{ optional($plantilla->updated_at)->format('d/m/Y H:i') }}</td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.plantillas-email.edit', $plantilla) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay plantillas disponibles para los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

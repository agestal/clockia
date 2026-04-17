@extends('layouts.app')

@section('title', 'Sesion del configurador')

@section('content_header_extra')
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div>
            <div class="d-flex align-items-center flex-wrap mb-2">
                <h1 class="mb-0 mr-2">Sesion #{{ $session->id }}</h1>
                <span class="badge {{ $session->statusBadgeClass() }}">{{ $session->statusLabel() }}</span>
            </div>
            <p class="text-muted mb-0">{{ $session->source_url }}</p>
        </div>

        <div class="mt-3 mt-md-0 d-flex flex-wrap">
            <form action="{{ route('admin.configurador-negocios.rediscover', $session) }}" method="POST" class="mr-2 mb-2">
                @csrf
                <button type="submit" class="btn btn-outline-primary">Relanzar descubrimiento</button>
            </form>

            @if($session->readyForProvisioning())
                <form action="{{ route('admin.configurador-negocios.provision', $session) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-primary">Crear negocio</button>
                </form>
            @endif
        </div>
    </div>
@stop

@section('content_body')
    @include('admin.partials.flash-messages')

    @if($errors->provision->any())
        <div class="alert alert-danger">
            <div class="font-weight-semibold mb-2">No se ha podido crear el negocio todavia.</div>
            <ul class="mb-0 pl-3">
                @foreach($errors->provision->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Resumen</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Host</dt>
                        <dd class="col-sm-7">{{ $session->source_host }}</dd>

                        <dt class="col-sm-5">Tipo</dt>
                        <dd class="col-sm-7">{{ $session->requestedTipoNegocio?->nombre ?: 'Sin definir' }}</dd>

                        <dt class="col-sm-5">Fuentes</dt>
                        <dd class="col-sm-7">{{ $session->sources->count() }}</dd>

                        <dt class="col-sm-5">Creada</dt>
                        <dd class="col-sm-7">{{ optional($session->created_at)->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-5">Explorada</dt>
                        <dd class="col-sm-7">{{ optional($session->discovery_finished_at)->format('d/m/Y H:i') ?: 'Todavia no' }}</dd>

                        <dt class="col-sm-5">Alta final</dt>
                        <dd class="col-sm-7">
                            @if($session->provisionedNegocio)
                                <a href="{{ route('admin.negocios.edit', $session->provisionedNegocio) }}" class="font-weight-semibold text-decoration-none">
                                    {{ $session->provisionedNegocio->nombre }}
                                </a>
                            @else
                                <span class="text-muted">Pendiente</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Campos pendientes</h2>
                </div>
                <div class="card-body">
                    @if($draft['missing_required_fields'] === [])
                        <p class="text-success mb-0">No quedan campos minimos pendientes. Ya puedes crear el negocio.</p>
                    @else
                        <ul class="mb-0 pl-3">
                            @foreach($draft['missing_required_fields'] as $field)
                                <li>{{ $field }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            @if($session->last_error)
                <div class="card shadow-sm border-0 border-danger">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0 text-danger">Ultimo error</h2>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $session->last_error }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Borrador de negocio</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Nombre</div>
                            <div class="font-weight-semibold">{{ $draft['business']['nombre'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">URL publica</div>
                            <div>{{ $draft['business']['url_publica'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Email</div>
                            <div>{{ $draft['business']['email'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Telefono</div>
                            <div>{{ $draft['business']['telefono'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Zona horaria</div>
                            <div>{{ $draft['business']['zona_horaria'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">Dias de apertura</div>
                            <div>
                                @php($days = [0 => 'Dom', 1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab'])
                                @if(! empty($draft['business']['dias_apertura']))
                                    {{ collect($draft['business']['dias_apertura'])->map(fn ($day) => $days[$day] ?? $day)->implode(', ') }}
                                @else
                                    Pendiente
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mb-0">
                            <div class="text-muted small">Descripcion publica</div>
                            <div>{{ $draft['business']['descripcion_publica'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="text-muted small">Direccion</div>
                            <div>{{ $draft['business']['direccion'] ?? 'Pendiente' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Administrador</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-muted small">Nombre</div>
                            <div>{{ $draft['admin']['name'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-muted small">Email</div>
                            <div>{{ $draft['admin']['email'] ?? 'Pendiente' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Contrasena</div>
                            <div>{{ ($draft['admin']['password_ready'] ?? false) ? 'Lista' : 'Pendiente' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Candidatas de experiencias</h2>
                </div>
                <div class="card-body">
                    @if($draft['experience_candidates'] === [])
                        <p class="text-muted mb-0">Todavia no hay experiencias detectadas con suficiente claridad.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($draft['experience_candidates'] as $candidate)
                                <div class="list-group-item px-0">
                                    <div class="font-weight-semibold">{{ $candidate['nombre'] ?? 'Experiencia sin nombre' }}</div>
                                    @if($candidate['descripcion'] ?? null)
                                        <div class="text-muted small mt-1">{{ $candidate['descripcion'] }}</div>
                                    @endif
                                    @if($candidate['source_url'] ?? null)
                                        <a href="{{ $candidate['source_url'] }}" target="_blank" rel="noopener" class="small text-decoration-none">Abrir fuente</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Horario detectado</h2>
                </div>
                <div class="card-body">
                    @if($draft['opening_hours'] === [])
                        <p class="text-muted mb-0">No se han detectado horarios estructurados.</p>
                    @else
                        <ul class="mb-0 pl-3">
                            @foreach($draft['opening_hours'] as $row)
                                <li>
                                    @if(is_array($row))
                                        @php($days = [0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado'])
                                        {{ collect($row['days'] ?? [])->map(fn ($day) => $days[$day] ?? $day)->implode(', ') }}
                                        @if(($row['opens'] ?? null) || ($row['closes'] ?? null))
                                            · {{ $row['opens'] ?? '--' }} - {{ $row['closes'] ?? '--' }}
                                        @endif
                                    @else
                                        {{ $row }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Notas del descubrimiento</h2>
                </div>
                <div class="card-body">
                    @if($draft['notes'] === [])
                        <p class="text-muted mb-0">Sin observaciones especiales.</p>
                    @else
                        <ul class="mb-0 pl-3">
                            @foreach($draft['notes'] as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Fuentes exploradas</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Rol</th>
                                    <th>Titulo</th>
                                    <th>HTTP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($session->sources as $source)
                                    <tr>
                                        <td class="align-middle">
                                            <a href="{{ $source->url }}" target="_blank" rel="noopener" class="text-decoration-none">{{ $source->url }}</a>
                                        </td>
                                        <td class="align-middle">{{ $source->page_role ?: 'general' }}</td>
                                        <td class="align-middle">{{ $source->title ?: 'Sin titulo' }}</td>
                                        <td class="align-middle">{{ $source->http_status ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No hay fuentes registradas todavia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <details class="mt-3">
                <summary class="text-muted">Ver borrador bruto</summary>
                <pre class="bg-light border rounded p-3 mt-2 mb-0" style="white-space: pre-wrap;">{{ json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </div>
    </div>
@stop

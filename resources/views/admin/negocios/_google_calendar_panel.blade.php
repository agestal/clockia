@php
    $googleAccount = $googleCalendarIntegration?->cuentaActiva;
    $calendarMappings = $googleCalendarIntegration?->mapeosCalendario ?? collect();
    $isEnabled = (bool) ($googleCalendarIntegration?->activo ?? false);
    $isConnected = $googleCalendarIntegration?->estaConectada() && $googleAccount;
    $defaultImportDays = (int) config('services.google_calendar.import_days', 30);
@endphp

<div class="card shadow-sm border-0 mt-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between">
            <div class="pr-md-4">
                <h2 class="h5 mb-1">Google Calendar</h2>
                <p class="text-muted mb-0">
                    Cuando esté activado, Clockia consultará Google al buscar huecos y enviará las reservas nuevas a los calendarios seleccionados.
                </p>
            </div>

            <div class="mt-3 mt-md-0 d-flex flex-wrap">
                <span class="badge {{ $isEnabled ? 'badge-success' : 'badge-secondary' }} mr-2 mb-2 px-3 py-2">
                    {{ $isEnabled ? 'Activado' : 'Desactivado' }}
                </span>
                <span class="badge {{ $isConnected ? 'badge-primary' : 'badge-light border' }} mb-2 px-3 py-2">
                    {{ $isConnected ? 'Cuenta conectada' : 'Sin conexión' }}
                </span>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <h3 class="h6 text-uppercase text-muted mb-3">Cuenta</h3>

                @if($googleAccount)
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Correo</dt>
                        <dd class="col-sm-8">{{ $googleAccount->email_externo ?: 'Sin dato' }}</dd>

                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8">{{ $googleAccount->nombre_externo ?: 'Google Calendar' }}</dd>

                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8 text-capitalize">{{ $googleCalendarIntegration?->estado ?: 'pendiente' }}</dd>

                        <dt class="col-sm-4">Expira</dt>
                        <dd class="col-sm-8">
                            {{ $googleAccount->token_expira_en?->timezone($negocio->zona_horaria)->format('d/m/Y H:i') ?: 'Sin fecha' }}
                        </dd>
                    </dl>
                @else
                    <p class="text-muted mb-3">
                        Autoriza una cuenta para empezar a consultar disponibilidad externa, seleccionar calendarios e importar ocupaciones.
                    </p>
                @endif

                <div class="d-flex flex-wrap mt-3">
                    <a
                        href="{{ route('admin.negocios.google-calendar.connect', $negocio) }}"
                        class="btn btn-primary mr-2 mb-2"
                    >
                        {{ $googleAccount ? 'Reconectar con Google' : 'Conectar con Google' }}
                    </a>

                    @if($googleAccount)
                        <form action="{{ route('admin.negocios.google-calendar.calendars.sync', $negocio) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-light border">Actualizar calendarios</button>
                        </form>
                    @endif
                </div>

                <p class="small text-muted mb-0">
                    Puedes dejar la cuenta conectada aunque el toggle del negocio esté desactivado.
                </p>
            </div>

            <div class="col-lg-7">
                <h3 class="h6 text-uppercase text-muted mb-3">Calendarios</h3>

                @if($calendarMappings->isEmpty())
                    <div class="alert alert-light border mb-4">
                        Todavía no hay calendarios disponibles. Conecta la cuenta y actualiza para traerlos desde Google.
                    </div>
                @else
                    <form action="{{ route('admin.negocios.google-calendar.calendars.update', $negocio) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-3">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">Usar</th>
                                        <th>Calendario</th>
                                        <th style="width: 220px;">Recurso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calendarMappings as $calendar)
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input"
                                                        id="google-calendar-selected-{{ $calendar->id }}"
                                                        name="selected[{{ $calendar->id }}]"
                                                        value="1"
                                                        @checked(old("selected.{$calendar->id}", $calendar->seleccionado))
                                                    >
                                                    <label class="custom-control-label" for="google-calendar-selected-{{ $calendar->id }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold">{{ $calendar->nombre_externo ?: $calendar->external_id }}</div>
                                                <div class="small text-muted">
                                                    {{ $calendar->external_id }}
                                                    @if($calendar->timezone)
                                                        · {{ $calendar->timezone }}
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    @if($calendar->es_primario)
                                                        <span class="badge badge-info">Principal</span>
                                                    @endif
                                                    @if(($calendar->datos_extra['access_role'] ?? null))
                                                        <span class="badge badge-light border text-uppercase">
                                                            {{ $calendar->datos_extra['access_role'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <select
                                                    name="resource_ids[{{ $calendar->id }}]"
                                                    class="form-control form-control-sm"
                                                >
                                                    <option value="">Todo el negocio</option>
                                                    @foreach($googleCalendarResources as $resource)
                                                        <option
                                                            value="{{ $resource->id }}"
                                                            @selected((string) old("resource_ids.{$calendar->id}", $calendar->recurso_id) === (string) $resource->id)
                                                        >
                                                            {{ $resource->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($errors->has('resource_ids.*'))
                            <div class="text-danger small mb-3">
                                {{ collect($errors->get('resource_ids.*'))->flatten()->first() }}
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">Guardar calendarios</button>
                    </form>
                @endif

                <hr class="my-4">

                <h3 class="h6 text-uppercase text-muted mb-3">Importación inicial</h3>

                <form action="{{ route('admin.negocios.google-calendar.import', $negocio) }}" method="POST" class="form-inline">
                    @csrf

                    <label for="google-calendar-days-ahead" class="mr-2">Próximos días</label>
                    <input
                        type="number"
                        min="1"
                        max="180"
                        id="google-calendar-days-ahead"
                        name="days_ahead"
                        value="{{ old('days_ahead', $defaultImportDays) }}"
                        class="form-control mr-2 mb-2 mb-sm-0"
                        style="width: 110px;"
                    >
                    <button type="submit" class="btn btn-outline-primary">Importar ahora</button>
                </form>

                @error('days_ahead')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                <p class="small text-muted mt-3 mb-0">
                    La importación se envía a cola para no frenar el backoffice.
                </p>
            </div>
        </div>
    </div>
</div>

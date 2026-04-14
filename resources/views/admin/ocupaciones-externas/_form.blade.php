@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-4">
                <label for="negocio_id" class="form-label">Negocio</label>
                <select
                    id="negocio_id"
                    name="negocio_id"
                    class="form-control @error('negocio_id') is-invalid @enderror"
                    required
                >
                    <option value="">— Selecciona un negocio —</option>
                    @foreach($negocios as $negocio)
                        <option value="{{ $negocio->id }}" @selected((string) old('negocio_id', $ocupacion->negocio_id) === (string) $negocio->id)>
                            {{ $negocio->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('negocio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="recurso_id" class="form-label">Recurso (opcional)</label>
                <select
                    id="recurso_id"
                    name="recurso_id"
                    class="form-control @error('recurso_id') is-invalid @enderror"
                >
                    <option value="">— Sin recurso —</option>
                    @foreach($recursos as $recurso)
                        <option value="{{ $recurso->id }}" @selected((string) old('recurso_id', $ocupacion->recurso_id) === (string) $recurso->id)>
                            {{ $recurso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="integracion_id" class="form-label">Integración (opcional)</label>
                <select
                    id="integracion_id"
                    name="integracion_id"
                    class="form-control @error('integracion_id') is-invalid @enderror"
                >
                    <option value="">— Sin integración —</option>
                    @foreach($integraciones as $integracion)
                        <option value="{{ $integracion->id }}" @selected((string) old('integracion_id', $ocupacion->integracion_id) === (string) $integracion->id)>
                            {{ $integracion->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('integracion_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-lg-4">
                <label for="proveedor" class="form-label">Proveedor</label>
                <select
                    id="proveedor"
                    name="proveedor"
                    class="form-control @error('proveedor') is-invalid @enderror"
                >
                    <option value="">— Sin proveedor —</option>
                    @foreach($proveedorOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('proveedor', $ocupacion->proveedor) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('proveedor')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="external_id" class="form-label">External ID</label>
                <input
                    type="text"
                    id="external_id"
                    name="external_id"
                    value="{{ old('external_id', $ocupacion->external_id) }}"
                    class="form-control @error('external_id') is-invalid @enderror"
                    maxlength="255"
                    required
                    placeholder="Identificador del evento externo"
                >
                @error('external_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="external_calendar_id" class="form-label">External Calendar ID</label>
                <input
                    type="text"
                    id="external_calendar_id"
                    name="external_calendar_id"
                    value="{{ old('external_calendar_id', $ocupacion->external_calendar_id) }}"
                    class="form-control @error('external_calendar_id') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Identificador del calendario externo"
                >
                @error('external_calendar_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-lg-4">
                <label for="titulo" class="form-label">Título</label>
                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    value="{{ old('titulo', $ocupacion->titulo) }}"
                    class="form-control @error('titulo') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Título del evento"
                >
                @error('titulo')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="origen" class="form-label">Origen</label>
                <input
                    type="text"
                    id="origen"
                    name="origen"
                    value="{{ old('origen', $ocupacion->origen) }}"
                    class="form-control @error('origen') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Origen de la ocupación"
                >
                @error('origen')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="estado" class="form-label">Estado</label>
                <input
                    type="text"
                    id="estado"
                    name="estado"
                    value="{{ old('estado', $ocupacion->estado) }}"
                    class="form-control @error('estado') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Estado de la ocupación"
                >
                @error('estado')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group col-12 px-0">
            <hr>
            <h3 class="h6 text-uppercase text-muted mb-3">Temporal</h3>
        </div>

        <div class="form-row">
            <div class="form-group col-lg-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="{{ old('fecha', optional($ocupacion->fecha)->format('Y-m-d')) }}"
                    class="form-control @error('fecha') is-invalid @enderror"
                >
                @error('fecha')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="hora_inicio" class="form-label">Hora de inicio</label>
                <input
                    type="time"
                    id="hora_inicio"
                    name="hora_inicio"
                    value="{{ old('hora_inicio', $horaInicioValue) }}"
                    class="form-control @error('hora_inicio') is-invalid @enderror"
                >
                @error('hora_inicio')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="hora_fin" class="form-label">Hora de fin</label>
                <input
                    type="time"
                    id="hora_fin"
                    name="hora_fin"
                    value="{{ old('hora_fin', $horaFinValue) }}"
                    class="form-control @error('hora_fin') is-invalid @enderror"
                >
                @error('hora_fin')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="es_dia_completo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('es_dia_completo') is-invalid @enderror"
                        id="es_dia_completo"
                        name="es_dia_completo"
                        value="1"
                        @checked(old('es_dia_completo', $ocupacion->es_dia_completo ?? false))
                    >
                    <label class="custom-control-label" for="es_dia_completo">Día completo</label>
                </div>
                @error('es_dia_completo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-lg-6">
                <label for="inicio_datetime" class="form-label">Inicio datetime</label>
                <input
                    type="datetime-local"
                    id="inicio_datetime"
                    name="inicio_datetime"
                    value="{{ old('inicio_datetime', optional($ocupacion->inicio_datetime)->format('Y-m-d\TH:i')) }}"
                    class="form-control @error('inicio_datetime') is-invalid @enderror"
                >
                @error('inicio_datetime')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="fin_datetime" class="form-label">Fin datetime</label>
                <input
                    type="datetime-local"
                    id="fin_datetime"
                    name="fin_datetime"
                    value="{{ old('fin_datetime', optional($ocupacion->fin_datetime)->format('Y-m-d\TH:i')) }}"
                    class="form-control @error('fin_datetime') is-invalid @enderror"
                >
                @error('fin_datetime')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <small class="form-text text-muted">
                Si rellenas inicio_datetime/fin_datetime, estos tienen prioridad sobre fecha+hora.
            </small>
        </div>

        <div class="form-group">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea
                id="descripcion"
                name="descripcion"
                class="form-control @error('descripcion') is-invalid @enderror"
                rows="3"
                placeholder="Descripción opcional del evento externo"
            >{{ old('descripcion', $ocupacion->descripcion) }}</textarea>
            @error('descripcion')
                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.ocupaciones-externas.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

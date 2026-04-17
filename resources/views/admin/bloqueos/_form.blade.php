@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-4">
                <label for="recurso_id" class="form-label">Recurso (opcional)</label>
                <select
                    id="recurso_id"
                    name="recurso_id"
                    class="form-control @error('recurso_id') is-invalid @enderror js-select2-recurso"
                    data-placeholder="Selecciona un recurso"
                >
                    <option value="">— Sin recurso (bloqueo de negocio) —</option>
                    @foreach($recursos as $recurso)
                        <option value="{{ $recurso->id }}" @selected((string) old('recurso_id', $bloqueo->recurso_id) === (string) $recurso->id)>
                            {{ $recurso->nombre }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Si no eliges recurso, el bloqueo será a nivel de negocio completo.</small>
                @error('recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="servicio_id" class="form-label">Experiencia (opcional)</label>
                <select
                    id="servicio_id"
                    name="servicio_id"
                    class="form-control @error('servicio_id') is-invalid @enderror js-select2-servicio"
                    data-placeholder="Selecciona una experiencia"
                >
                    <option value="">— Sin experiencia específica —</option>
                    @foreach($servicios as $servicio)
                        <option value="{{ $servicio->id }}" @selected((string) old('servicio_id', $bloqueo->servicio_id) === (string) $servicio->id)>
                            {{ $servicio->nombre }} @if($servicio->negocio)· {{ $servicio->negocio->nombre }} @endif
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Úsalo para bloquear solo una experiencia en una o varias franjas.</small>
                @error('servicio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="negocio_id" class="form-label">Negocio</label>
                <select
                    id="negocio_id"
                    name="negocio_id"
                    class="form-control @error('negocio_id') is-invalid @enderror js-select2-negocio"
                    data-placeholder="Selecciona un negocio"
                >
                    <option value="">— Sin negocio —</option>
                    @foreach($negocios as $negocio)
                        <option value="{{ $negocio->id }}" @selected((string) old('negocio_id', $bloqueo->negocio_id) === (string) $negocio->id)>
                            {{ $negocio->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('negocio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Si eliges una experiencia, el negocio se completa automáticamente.</small>
            </div>

            <div class="form-group col-lg-6">
                <label for="tipo_bloqueo_id" class="form-label">Tipo de bloqueo</label>
                <select
                    id="tipo_bloqueo_id"
                    name="tipo_bloqueo_id"
                    class="form-control @error('tipo_bloqueo_id') is-invalid @enderror js-select2-tipo-bloqueo"
                    required
                    data-placeholder="Selecciona un tipo de bloqueo"
                >
                    <option value=""></option>
                    @foreach($tiposBloqueo as $tipoBloqueo)
                        <option value="{{ $tipoBloqueo->id }}" @selected((string) old('tipo_bloqueo_id', $bloqueo->tipo_bloqueo_id) === (string) $tipoBloqueo->id)>
                            {{ $tipoBloqueo->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_bloqueo_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="es_recurrente" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('es_recurrente') is-invalid @enderror"
                        id="es_recurrente"
                        name="es_recurrente"
                        value="1"
                        @checked(old('es_recurrente', $bloqueo->es_recurrente ?? false))
                    >
                    <label class="custom-control-label" for="es_recurrente">Recurrente</label>
                </div>
                @error('es_recurrente')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('activo') is-invalid @enderror"
                        id="activo"
                        name="activo"
                        value="1"
                        @checked(old('activo', $bloqueo->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <hr>
                <h3 class="h6 text-uppercase text-muted mb-3">Fechas</h3>
                <p class="small text-muted">Rellena uno de los tres modos: fecha puntual, rango de fechas o día de la semana si es recurrente.</p>
            </div>

            <div class="form-group col-lg-4">
                <label for="fecha" class="form-label">Fecha puntual</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="{{ old('fecha', optional($bloqueo->fecha)->format('Y-m-d')) }}"
                    class="form-control @error('fecha') is-invalid @enderror"
                >
                @error('fecha')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="fecha_inicio" class="form-label">Fecha inicio rango</label>
                <input
                    type="date"
                    id="fecha_inicio"
                    name="fecha_inicio"
                    value="{{ old('fecha_inicio', optional($bloqueo->fecha_inicio)->format('Y-m-d')) }}"
                    class="form-control @error('fecha_inicio') is-invalid @enderror"
                >
                @error('fecha_inicio')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="fecha_fin" class="form-label">Fecha fin rango</label>
                <input
                    type="date"
                    id="fecha_fin"
                    name="fecha_fin"
                    value="{{ old('fecha_fin', optional($bloqueo->fecha_fin)->format('Y-m-d')) }}"
                    class="form-control @error('fecha_fin') is-invalid @enderror"
                >
                @error('fecha_fin')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="dia_semana" class="form-label">Día de la semana (recurrente)</label>
                <select
                    id="dia_semana"
                    name="dia_semana"
                    class="form-control @error('dia_semana') is-invalid @enderror"
                >
                    <option value="">— No aplica —</option>
                    @foreach($dayOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('dia_semana', $bloqueo->dia_semana) === (string) $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('dia_semana')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
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

            <div class="form-group col-lg-4">
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

            <div class="form-group col-12">
                <small class="form-text text-muted js-time-helper">
                    Si dejas hora_inicio y hora_fin vacías, el bloqueo se considerará de día completo.
                </small>
            </div>

            <div class="form-group col-12">
                <label for="motivo" class="form-label">Motivo</label>
                <input
                    type="text"
                    id="motivo"
                    name="motivo"
                    value="{{ old('motivo', $bloqueo->motivo) }}"
                    class="form-control @error('motivo') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Motivo opcional del bloqueo"
                >
                @error('motivo')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.bloqueos.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

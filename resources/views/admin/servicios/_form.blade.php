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
                    class="form-control @error('negocio_id') is-invalid @enderror js-select2-negocio"
                    required
                    data-placeholder="Selecciona un negocio"
                >
                    @if($selectedNegocio)
                        <option value="{{ $selectedNegocio->id }}" selected>{{ $selectedNegocio->nombre }}</option>
                    @endif
                </select>
                @error('negocio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="tipo_precio_id" class="form-label">Tipo de precio</label>
                <select
                    id="tipo_precio_id"
                    name="tipo_precio_id"
                    class="form-control @error('tipo_precio_id') is-invalid @enderror js-select2-tipo-precio"
                    required
                    data-placeholder="Selecciona un tipo de precio"
                >
                    @if($selectedTipoPrecio)
                        <option value="{{ $selectedTipoPrecio->id }}" selected>{{ $selectedTipoPrecio->nombre }}</option>
                    @endif
                </select>
                @error('tipo_precio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $servicio->nombre) }}"
                    class="form-control @error('nombre') is-invalid @enderror"
                    maxlength="255"
                    minlength="2"
                    required
                    autofocus
                    placeholder="Ejemplo: Sesión inicial"
                >
                @error('nombre')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="duracion_minutos" class="form-label">Duración (minutos)</label>
                <input
                    type="number"
                    id="duracion_minutos"
                    name="duracion_minutos"
                    value="{{ old('duracion_minutos', $servicio->duracion_minutos) }}"
                    class="form-control @error('duracion_minutos') is-invalid @enderror"
                    min="1"
                    step="1"
                    required
                    placeholder="Ejemplo: 60"
                >
                @error('duracion_minutos')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="precio_base" class="form-label">Precio base</label>
                <input
                    type="number"
                    id="precio_base"
                    name="precio_base"
                    value="{{ old('precio_base', $servicio->precio_base) }}"
                    class="form-control @error('precio_base') is-invalid @enderror"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    required
                    placeholder="Ejemplo: 49.90"
                >
                @error('precio_base')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="requiere_pago" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('requiere_pago') is-invalid @enderror"
                        id="requiere_pago"
                        name="requiere_pago"
                        value="1"
                        @checked(old('requiere_pago', $servicio->requiere_pago ?? false))
                    >
                    <label class="custom-control-label" for="requiere_pago">Requiere pago</label>
                </div>
                @error('requiere_pago')
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
                        @checked(old('activo', $servicio->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="es_reembolsable" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('es_reembolsable') is-invalid @enderror"
                        id="es_reembolsable"
                        name="es_reembolsable"
                        value="1"
                        @checked(old('es_reembolsable', $servicio->es_reembolsable ?? true))
                    >
                    <label class="custom-control-label" for="es_reembolsable">Reembolsable</label>
                </div>
                @error('es_reembolsable')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="precio_por_unidad_tiempo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('precio_por_unidad_tiempo') is-invalid @enderror"
                        id="precio_por_unidad_tiempo"
                        name="precio_por_unidad_tiempo"
                        value="1"
                        @checked(old('precio_por_unidad_tiempo', $servicio->precio_por_unidad_tiempo ?? false))
                    >
                    <label class="custom-control-label" for="precio_por_unidad_tiempo">Precio por unidad de tiempo</label>
                </div>
                @error('precio_por_unidad_tiempo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="horas_minimas_cancelacion" class="form-label">Horas mín. cancelación</label>
                <input
                    type="number"
                    id="horas_minimas_cancelacion"
                    name="horas_minimas_cancelacion"
                    value="{{ old('horas_minimas_cancelacion', $servicio->horas_minimas_cancelacion) }}"
                    class="form-control @error('horas_minimas_cancelacion') is-invalid @enderror"
                    min="0"
                    step="1"
                    placeholder="Ejemplo: 24"
                >
                @error('horas_minimas_cancelacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="porcentaje_senal" class="form-label">Porcentaje de señal (%)</label>
                <input
                    type="number"
                    id="porcentaje_senal"
                    name="porcentaje_senal"
                    value="{{ old('porcentaje_senal', $servicio->porcentaje_senal) }}"
                    class="form-control @error('porcentaje_senal') is-invalid @enderror"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="Ejemplo: 20.00"
                >
                <small class="form-text text-muted">Porcentaje del precio base que se cobra como señal.</small>
                @error('porcentaje_senal')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="recursos" class="form-label">Recursos asociados</label>
                <select
                    id="recursos"
                    name="recursos[]"
                    class="form-control @error('recursos') is-invalid @enderror @error('recursos.*') is-invalid @enderror js-select2-recursos"
                    multiple
                    data-placeholder="Selecciona uno o varios recursos"
                >
                    @foreach($selectedRecursos as $recurso)
                        <option value="{{ $recurso->id }}" selected>
                            {{ $recurso->nombre }} · {{ $recurso->negocio?->nombre }} · {{ $recurso->tipoRecurso?->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('recursos')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
                @error('recursos.*')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
                <p class="small text-muted mt-2 mb-0">Aquí se sincroniza la relación entre servicios y recursos en esta primera versión.</p>
            </div>

            <div class="form-group col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    placeholder="Describe brevemente el servicio"
                >{{ old('descripcion', $servicio->descripcion) }}</textarea>
                @error('descripcion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="notas_publicas" class="form-label">Notas públicas</label>
                <textarea
                    id="notas_publicas"
                    name="notas_publicas"
                    rows="4"
                    class="form-control @error('notas_publicas') is-invalid @enderror"
                    placeholder="Información visible para el cliente sobre el servicio"
                >{{ old('notas_publicas', $servicio->notas_publicas) }}</textarea>
                @error('notas_publicas')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="instrucciones_previas" class="form-label">Instrucciones previas</label>
                <textarea
                    id="instrucciones_previas"
                    name="instrucciones_previas"
                    rows="4"
                    class="form-control @error('instrucciones_previas') is-invalid @enderror"
                    placeholder="Qué debe hacer o traer el cliente antes del servicio"
                >{{ old('instrucciones_previas', $servicio->instrucciones_previas) }}</textarea>
                @error('instrucciones_previas')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="documentacion_requerida" class="form-label">Documentación requerida</label>
                <textarea
                    id="documentacion_requerida"
                    name="documentacion_requerida"
                    rows="4"
                    class="form-control @error('documentacion_requerida') is-invalid @enderror"
                    placeholder="Documentación específica necesaria para este servicio"
                >{{ old('documentacion_requerida', $servicio->documentacion_requerida) }}</textarea>
                @error('documentacion_requerida')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.servicios.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

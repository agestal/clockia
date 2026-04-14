@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-3">
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

            <div class="form-group col-lg-3">
                <label for="servicio_id" class="form-label">Servicio</label>
                <select
                    id="servicio_id"
                    name="servicio_id"
                    class="form-control @error('servicio_id') is-invalid @enderror js-select2-servicio"
                    required
                    data-placeholder="Selecciona un servicio"
                >
                    @if($selectedServicio)
                        <option value="{{ $selectedServicio->id }}" selected>{{ $selectedServicio->nombre }}</option>
                    @endif
                </select>
                @error('servicio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="recurso_id" class="form-label">Recurso</label>
                <select
                    id="recurso_id"
                    name="recurso_id"
                    class="form-control @error('recurso_id') is-invalid @enderror js-select2-recurso"
                    required
                    data-placeholder="Selecciona un recurso"
                >
                    @if($selectedRecurso)
                        <option value="{{ $selectedRecurso->id }}" selected>{{ $selectedRecurso->nombre }}</option>
                    @endif
                </select>
                @error('recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select
                    id="cliente_id"
                    name="cliente_id"
                    class="form-control @error('cliente_id') is-invalid @enderror js-select2-cliente"
                    required
                    data-placeholder="Selecciona un cliente"
                >
                    @if($selectedCliente)
                        <option value="{{ $selectedCliente->id }}" selected>
                            {{ implode(' · ', array_filter([$selectedCliente->nombre, $selectedCliente->email, $selectedCliente->telefono])) }}
                        </option>
                    @endif
                </select>
                @error('cliente_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    value="{{ old('fecha', $reserva->fecha?->format('Y-m-d')) }}"
                    class="form-control @error('fecha') is-invalid @enderror"
                    required
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
                    value="{{ old('hora_inicio', $reserva->hora_inicio ? substr((string) $reserva->hora_inicio, 0, 5) : null) }}"
                    class="form-control @error('hora_inicio') is-invalid @enderror"
                    required
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
                    value="{{ old('hora_fin', $reserva->hora_fin ? substr((string) $reserva->hora_fin, 0, 5) : null) }}"
                    class="form-control @error('hora_fin') is-invalid @enderror"
                    required
                >
                @error('hora_fin')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="estado_reserva_id" class="form-label">Estado de reserva</label>
                <select
                    id="estado_reserva_id"
                    name="estado_reserva_id"
                    class="form-control @error('estado_reserva_id') is-invalid @enderror js-select2-estado-reserva"
                    required
                    data-placeholder="Selecciona un estado de reserva"
                >
                    @if($selectedEstadoReserva)
                        <option value="{{ $selectedEstadoReserva->id }}" selected>{{ $selectedEstadoReserva->nombre }}</option>
                    @endif
                </select>
                @error('estado_reserva_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="numero_personas" class="form-label">Número de personas</label>
                <input
                    type="number"
                    id="numero_personas"
                    name="numero_personas"
                    value="{{ old('numero_personas', $reserva->numero_personas) }}"
                    class="form-control @error('numero_personas') is-invalid @enderror"
                    min="1"
                    step="1"
                    placeholder="Ejemplo: 2"
                >
                @error('numero_personas')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="precio_calculado" class="form-label">Precio calculado</label>
                <input
                    type="number"
                    id="precio_calculado"
                    name="precio_calculado"
                    value="{{ old('precio_calculado', $reserva->precio_calculado) }}"
                    class="form-control @error('precio_calculado') is-invalid @enderror"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    required
                >
                @error('precio_calculado')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="precio_total" class="form-label">Precio total</label>
                <input
                    type="number"
                    id="precio_total"
                    name="precio_total"
                    value="{{ old('precio_total', $reserva->precio_total) }}"
                    class="form-control @error('precio_total') is-invalid @enderror"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    placeholder="Opcional"
                >
                @error('precio_total')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="notas" class="form-label">Notas</label>
                <textarea
                    id="notas"
                    name="notas"
                    rows="4"
                    class="form-control @error('notas') is-invalid @enderror"
                    placeholder="Añade observaciones internas si lo necesitas"
                >{{ old('notas', $reserva->notas) }}</textarea>
                @error('notas')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            @if($isEdit && $reserva->localizador)
                <div class="form-group col-lg-4">
                    <label class="form-label">Localizador</label>
                    <input
                        type="text"
                        value="{{ $reserva->localizador }}"
                        class="form-control"
                        readonly
                        disabled
                    >
                </div>
            @endif

            <div class="form-group col-lg-4">
                <label for="fecha_estimada_fin" class="form-label">Fecha estimada de fin</label>
                <input
                    type="datetime-local"
                    id="fecha_estimada_fin"
                    name="fecha_estimada_fin"
                    value="{{ old('fecha_estimada_fin', $reserva->fecha_estimada_fin?->format('Y-m-d\\TH:i')) }}"
                    class="form-control @error('fecha_estimada_fin') is-invalid @enderror"
                >
                @error('fecha_estimada_fin')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="documentacion_entregada" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('documentacion_entregada') is-invalid @enderror"
                        id="documentacion_entregada"
                        name="documentacion_entregada"
                        value="1"
                        @checked(old('documentacion_entregada', $reserva->documentacion_entregada ?? false))
                    >
                    <label class="custom-control-label" for="documentacion_entregada">Documentación entregada</label>
                </div>
                @error('documentacion_entregada')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="instrucciones_llegada" class="form-label">Instrucciones de llegada</label>
                <textarea
                    id="instrucciones_llegada"
                    name="instrucciones_llegada"
                    rows="4"
                    class="form-control @error('instrucciones_llegada') is-invalid @enderror"
                    placeholder="Instrucciones específicas para esta reserva"
                >{{ old('instrucciones_llegada', $reserva->instrucciones_llegada) }}</textarea>
                @error('instrucciones_llegada')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="motivo_cancelacion" class="form-label">Motivo de cancelación</label>
                <textarea
                    id="motivo_cancelacion"
                    name="motivo_cancelacion"
                    rows="3"
                    class="form-control @error('motivo_cancelacion') is-invalid @enderror"
                    placeholder="Motivo por el que se canceló la reserva"
                >{{ old('motivo_cancelacion', $reserva->motivo_cancelacion) }}</textarea>
                @error('motivo_cancelacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="cancelada_por" class="form-label">Cancelada por</label>
                <select
                    id="cancelada_por"
                    name="cancelada_por"
                    class="form-control @error('cancelada_por') is-invalid @enderror"
                >
                    <option value="">— Sin cancelar —</option>
                    <option value="cliente" @selected(old('cancelada_por', $reserva->cancelada_por) === 'cliente')>Cliente</option>
                    <option value="negocio" @selected(old('cancelada_por', $reserva->cancelada_por) === 'negocio')>Negocio</option>
                    <option value="sistema" @selected(old('cancelada_por', $reserva->cancelada_por) === 'sistema')>Sistema</option>
                </select>
                @error('cancelada_por')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <hr>
                <h3 class="h6 text-uppercase text-muted mb-3">Overrides de política</h3>
                <p class="small text-muted">Si dejas un campo vacío, la reserva hereda la política del servicio o del negocio. Solo rellena los campos que quieras sobreescribir.</p>
            </div>

            <div class="form-group col-lg-3">
                <label for="horas_minimas_cancelacion" class="form-label">Horas mín. cancelación</label>
                <input
                    type="number"
                    id="horas_minimas_cancelacion"
                    name="horas_minimas_cancelacion"
                    value="{{ old('horas_minimas_cancelacion', $reserva->horas_minimas_cancelacion) }}"
                    class="form-control @error('horas_minimas_cancelacion') is-invalid @enderror"
                    min="0"
                    step="1"
                    placeholder="Hereda del servicio/negocio"
                >
                @error('horas_minimas_cancelacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="porcentaje_senal" class="form-label">Porcentaje señal (%)</label>
                <input
                    type="number"
                    id="porcentaje_senal"
                    name="porcentaje_senal"
                    value="{{ old('porcentaje_senal', $reserva->porcentaje_senal) }}"
                    class="form-control @error('porcentaje_senal') is-invalid @enderror"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="Hereda del servicio"
                >
                @error('porcentaje_senal')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="permite_modificacion" class="form-label">Permite modificación</label>
                <select
                    id="permite_modificacion"
                    name="permite_modificacion"
                    class="form-control @error('permite_modificacion') is-invalid @enderror"
                >
                    <option value="" @selected(old('permite_modificacion', $reserva->permite_modificacion) === null)>— Heredar —</option>
                    <option value="1" @selected((string) old('permite_modificacion', $reserva->permite_modificacion) === '1')>Sí</option>
                    <option value="0" @selected((string) old('permite_modificacion', $reserva->permite_modificacion) === '0' && old('permite_modificacion', $reserva->permite_modificacion) !== null)>No</option>
                </select>
                @error('permite_modificacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="es_reembolsable" class="form-label">Es reembolsable</label>
                <select
                    id="es_reembolsable"
                    name="es_reembolsable"
                    class="form-control @error('es_reembolsable') is-invalid @enderror"
                >
                    <option value="" @selected(old('es_reembolsable', $reserva->es_reembolsable) === null)>— Heredar —</option>
                    <option value="1" @selected((string) old('es_reembolsable', $reserva->es_reembolsable) === '1')>Sí</option>
                    <option value="0" @selected((string) old('es_reembolsable', $reserva->es_reembolsable) === '0' && old('es_reembolsable', $reserva->es_reembolsable) !== null)>No</option>
                </select>
                @error('es_reembolsable')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.reservas.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

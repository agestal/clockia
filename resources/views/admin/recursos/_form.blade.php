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
                <label for="tipo_recurso_id" class="form-label">Tipo de recurso</label>
                <select
                    id="tipo_recurso_id"
                    name="tipo_recurso_id"
                    class="form-control @error('tipo_recurso_id') is-invalid @enderror js-select2-tipo-recurso"
                    required
                    data-placeholder="Selecciona un tipo de recurso"
                >
                    @if($selectedTipoRecurso)
                        <option value="{{ $selectedTipoRecurso->id }}" selected>{{ $selectedTipoRecurso->nombre }}</option>
                    @endif
                </select>
                @error('tipo_recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $recurso->nombre) }}"
                    class="form-control @error('nombre') is-invalid @enderror"
                    maxlength="255"
                    minlength="2"
                    required
                    autofocus
                    placeholder="Ejemplo: Sala principal"
                >
                @error('nombre')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="capacidad" class="form-label">Capacidad máxima</label>
                <input
                    type="number"
                    id="capacidad"
                    name="capacidad"
                    value="{{ old('capacidad', $recurso->capacidad) }}"
                    class="form-control @error('capacidad') is-invalid @enderror"
                    min="1"
                    step="1"
                    placeholder="Ejemplo: 8"
                >
                @error('capacidad')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3">
                <label for="capacidad_minima" class="form-label">Capacidad mínima</label>
                <input
                    type="number"
                    id="capacidad_minima"
                    name="capacidad_minima"
                    value="{{ old('capacidad_minima', $recurso->capacidad_minima) }}"
                    class="form-control @error('capacidad_minima') is-invalid @enderror"
                    min="1"
                    step="1"
                    placeholder="Ejemplo: 2"
                >
                <small class="form-text text-muted">Mínimo de personas para reservar este recurso.</small>
                @error('capacidad_minima')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-3 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="combinable" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('combinable') is-invalid @enderror"
                        id="combinable"
                        name="combinable"
                        value="1"
                        @checked(old('combinable', $recurso->combinable ?? false))
                    >
                    <label class="custom-control-label" for="combinable">Combinable</label>
                </div>
                @error('combinable')
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
                        @checked(old('activo', $recurso->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="notas_publicas" class="form-label">Notas públicas</label>
                <textarea
                    id="notas_publicas"
                    name="notas_publicas"
                    rows="4"
                    class="form-control @error('notas_publicas') is-invalid @enderror"
                    placeholder="Información visible para el cliente sobre el recurso"
                >{{ old('notas_publicas', $recurso->notas_publicas) }}</textarea>
                @error('notas_publicas')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>

        @if($isEdit)
            <div class="border-top pt-3 mt-2">
                <h3 class="h6 mb-3">Servicios asociados</h3>
                @if($servicios->isEmpty())
                    <p class="text-muted mb-0">Este recurso no tiene servicios asociados.</p>
                @else
                    <div class="d-flex flex-wrap">
                        @foreach($servicios as $servicio)
                            <span class="badge badge-light border mr-2 mb-2 px-3 py-2">{{ $servicio->nombre }}</span>
                        @endforeach
                    </div>
                    <p class="small text-muted mb-0">La asociación con servicios se gestiona desde el CRUD de Servicio.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.recursos.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

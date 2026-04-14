@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-6">
                <label for="integracion_id" class="form-label">Integración</label>
                <select
                    id="integracion_id"
                    name="integracion_id"
                    class="form-control @error('integracion_id') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona una integración</option>
                    @foreach($integraciones as $integracion)
                        <option value="{{ $integracion->id }}" @selected(old('integracion_id', $mapeo->integracion_id) == $integracion->id)>
                            {{ $integracion->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('integracion_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="tipo_origen" class="form-label">Tipo de origen</label>
                <select
                    id="tipo_origen"
                    name="tipo_origen"
                    class="form-control @error('tipo_origen') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un tipo de origen</option>
                    @foreach($tipoOrigenOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('tipo_origen', $mapeo->tipo_origen) === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_origen')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="external_id" class="form-label">External ID</label>
                <input
                    type="text"
                    id="external_id"
                    name="external_id"
                    value="{{ old('external_id', $mapeo->external_id) }}"
                    class="form-control @error('external_id') is-invalid @enderror"
                    maxlength="255"
                    required
                    placeholder="Identificador en el sistema externo"
                >
                @error('external_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="external_parent_id" class="form-label">External Parent ID</label>
                <input
                    type="text"
                    id="external_parent_id"
                    name="external_parent_id"
                    value="{{ old('external_parent_id', $mapeo->external_parent_id) }}"
                    class="form-control @error('external_parent_id') is-invalid @enderror"
                    maxlength="255"
                    placeholder="ID del padre en el sistema externo (opcional)"
                >
                @error('external_parent_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="nombre_externo" class="form-label">Nombre externo</label>
                <input
                    type="text"
                    id="nombre_externo"
                    name="nombre_externo"
                    value="{{ old('nombre_externo', $mapeo->nombre_externo) }}"
                    class="form-control @error('nombre_externo') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Nombre en el sistema externo (opcional)"
                >
                @error('nombre_externo')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('activo') is-invalid @enderror"
                        id="activo"
                        name="activo"
                        value="1"
                        @checked(old('activo', $mapeo->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="negocio_id" class="form-label">Negocio</label>
                <select
                    id="negocio_id"
                    name="negocio_id"
                    class="form-control @error('negocio_id') is-invalid @enderror"
                >
                    <option value="">Sin negocio</option>
                    @foreach($negocios as $negocio)
                        <option value="{{ $negocio->id }}" @selected(old('negocio_id', $mapeo->negocio_id) == $negocio->id)>
                            {{ $negocio->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('negocio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="recurso_id" class="form-label">Recurso</label>
                <select
                    id="recurso_id"
                    name="recurso_id"
                    class="form-control @error('recurso_id') is-invalid @enderror"
                >
                    <option value="">Sin recurso</option>
                    @foreach($recursos as $recurso)
                        <option value="{{ $recurso->id }}" @selected(old('recurso_id', $mapeo->recurso_id) == $recurso->id)>
                            {{ $recurso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="servicio_id" class="form-label">Servicio</label>
                <select
                    id="servicio_id"
                    name="servicio_id"
                    class="form-control @error('servicio_id') is-invalid @enderror"
                >
                    <option value="">Sin servicio</option>
                    @foreach($servicios as $servicio)
                        <option value="{{ $servicio->id }}" @selected(old('servicio_id', $mapeo->servicio_id) == $servicio->id)>
                            {{ $servicio->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('servicio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="configuracion" class="form-label">Configuración</label>
                <textarea
                    id="configuracion"
                    name="configuracion"
                    rows="4"
                    class="form-control @error('configuracion') is-invalid @enderror"
                    placeholder="JSON de configuración adicional (opcional)"
                >{{ old('configuracion', $mapeo->configuracion ? json_encode($mapeo->configuracion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                @error('configuracion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.integracion-mapeos.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

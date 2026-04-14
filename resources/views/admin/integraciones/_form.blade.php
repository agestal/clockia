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
                    <option value="">Selecciona un negocio</option>
                    @foreach($negocios as $negocio)
                        <option value="{{ $negocio->id }}" @selected(old('negocio_id', $integracion->negocio_id) == $negocio->id)>{{ $negocio->nombre }}</option>
                    @endforeach
                </select>
                @error('negocio_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="proveedor" class="form-label">Proveedor</label>
                <select
                    id="proveedor"
                    name="proveedor"
                    class="form-control @error('proveedor') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un proveedor</option>
                    @foreach($proveedorOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('proveedor', $integracion->proveedor) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('proveedor')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $integracion->nombre) }}"
                    class="form-control @error('nombre') is-invalid @enderror"
                    maxlength="255"
                    minlength="2"
                    required
                    autofocus
                    placeholder="Ejemplo: Google Calendar Principal"
                >
                @error('nombre')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="modo_operacion" class="form-label">Modo de operación</label>
                <select
                    id="modo_operacion"
                    name="modo_operacion"
                    class="form-control @error('modo_operacion') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un modo</option>
                    @foreach($modoOperacionOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('modo_operacion', $integracion->modo_operacion) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('modo_operacion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="estado" class="form-label">Estado</label>
                <select
                    id="estado"
                    name="estado"
                    class="form-control @error('estado') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un estado</option>
                    @foreach($estadoOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('estado', $integracion->estado) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('estado')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4 d-flex align-items-center">
                <div class="custom-control custom-switch mt-4">
                    <input type="hidden" name="activo" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('activo') is-invalid @enderror"
                        id="activo"
                        name="activo"
                        value="1"
                        @checked(old('activo', $integracion->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activo</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="configuracion" class="form-label">Configuración (JSON libre)</label>
                <textarea
                    id="configuracion"
                    name="configuracion"
                    rows="5"
                    class="form-control @error('configuracion') is-invalid @enderror"
                    placeholder='{"clave": "valor"}'
                >{{ old('configuracion', $integracion->configuracion ? json_encode($integracion->configuracion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                <small class="form-text text-muted">Introduce un JSON válido o deja vacío. Si el formato no es válido se guardará como nulo.</small>
                @error('configuracion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            @if($isEdit && $integracion->ultimo_sync_at)
                <div class="form-group col-lg-6">
                    <label class="form-label">Última sincronización</label>
                    <input type="text" class="form-control" value="{{ $integracion->ultimo_sync_at->format('d/m/Y H:i') }}" readonly>
                </div>
            @endif

            @if($isEdit && $integracion->ultimo_error)
                <div class="form-group col-lg-6">
                    <label class="form-label">Último error</label>
                    <input type="text" class="form-control text-danger" value="{{ $integracion->ultimo_error }}" readonly>
                </div>
            @endif
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.integraciones.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-6">
                <label for="recurso_id" class="form-label">Recurso</label>
                <select
                    id="recurso_id"
                    name="recurso_id"
                    class="form-control @error('recurso_id') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un recurso</option>
                    @foreach($recursos as $recurso)
                        <option value="{{ $recurso->id }}" @selected((string) old('recurso_id', $combinacion->recurso_id) === (string) $recurso->id)>
                            {{ $recurso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="recurso_combinado_id" class="form-label">Recurso combinado</label>
                <select
                    id="recurso_combinado_id"
                    name="recurso_combinado_id"
                    class="form-control @error('recurso_combinado_id') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un recurso</option>
                    @foreach($recursos as $recurso)
                        <option value="{{ $recurso->id }}" @selected((string) old('recurso_combinado_id', $combinacion->recurso_combinado_id) === (string) $recurso->id)>
                            {{ $recurso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('recurso_combinado_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.recurso-combinaciones.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

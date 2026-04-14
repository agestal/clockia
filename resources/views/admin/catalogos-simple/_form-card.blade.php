@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-6">
                <label for="nombre" class="form-label">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $item->nombre) }}"
                    class="form-control @error('nombre') is-invalid @enderror"
                    maxlength="255"
                    minlength="2"
                    required
                    autofocus
                    placeholder="{{ $nombrePlaceholder }}"
                >
                <small class="form-text text-muted">{{ $nombreHelp }}</small>
                @error('nombre')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    placeholder="{{ $descripcionPlaceholder }}"
                >{{ old('descripcion', $item->descripcion) }}</textarea>
                @error('descripcion')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ $backRoute }}" class="btn btn-light border">Volver</a>

        <button type="submit" class="btn btn-primary">
            {{ $submitLabel }}
        </button>
    </div>
</div>

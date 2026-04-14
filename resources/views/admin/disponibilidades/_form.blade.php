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
                    class="form-control @error('recurso_id') is-invalid @enderror js-select2-recurso"
                    required
                    data-placeholder="Selecciona un recurso"
                >
                    <option value=""></option>
                    @foreach($recursos as $recurso)
                        <option value="{{ $recurso->id }}" @selected((string) old('recurso_id', $disponibilidad->recurso_id) === (string) $recurso->id)>
                            {{ $recurso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('recurso_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="dia_semana" class="form-label">Día de la semana</label>
                <select
                    id="dia_semana"
                    name="dia_semana"
                    class="form-control @error('dia_semana') is-invalid @enderror"
                    required
                >
                    <option value="">Selecciona un día</option>
                    @foreach($dayOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('dia_semana', $disponibilidad->dia_semana) === (string) $value)>
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
                    required
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
                    required
                >
                @error('hora_fin')
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
                        @checked(old('activo', $disponibilidad->activo ?? true))
                    >
                    <label class="custom-control-label" for="activo">Activa</label>
                </div>
                @error('activo')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="nombre_turno" class="form-label">Nombre del turno</label>
                <input
                    type="text"
                    id="nombre_turno"
                    name="nombre_turno"
                    value="{{ old('nombre_turno', $disponibilidad->nombre_turno) }}"
                    class="form-control @error('nombre_turno') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Ejemplo: Turno de comida"
                >
                @error('nombre_turno')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="buffer_minutos" class="form-label">Buffer entre reservas (minutos)</label>
                <input
                    type="number"
                    id="buffer_minutos"
                    name="buffer_minutos"
                    value="{{ old('buffer_minutos', $disponibilidad->buffer_minutos) }}"
                    class="form-control @error('buffer_minutos') is-invalid @enderror"
                    min="0"
                    step="1"
                    placeholder="Ejemplo: 15"
                >
                <small class="form-text text-muted">Minutos de margen entre una reserva y la siguiente en este tramo.</small>
                @error('buffer_minutos')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.disponibilidades.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

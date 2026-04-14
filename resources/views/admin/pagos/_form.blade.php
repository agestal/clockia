@csrf

@if($isEdit)
    @method('PUT')
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-lg-4">
                <label for="reserva_id" class="form-label">Reserva</label>
                <select
                    id="reserva_id"
                    name="reserva_id"
                    class="form-control @error('reserva_id') is-invalid @enderror js-select2-reserva"
                    required
                    data-placeholder="Selecciona una reserva"
                >
                    @if($selectedReserva)
                        <option value="{{ $selectedReserva->id }}" selected>
                            {{ 'Reserva #'.$selectedReserva->id.' · '.optional($selectedReserva->fecha)->format('d/m/Y').' · '.substr((string) $selectedReserva->hora_inicio, 0, 5).' · '.($selectedReserva->cliente?->nombre ?? '—').' · '.($selectedReserva->servicio?->nombre ?? '—') }}
                        </option>
                    @endif
                </select>
                @error('reserva_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="tipo_pago_id" class="form-label">Tipo de pago</label>
                <select
                    id="tipo_pago_id"
                    name="tipo_pago_id"
                    class="form-control @error('tipo_pago_id') is-invalid @enderror js-select2-tipo-pago"
                    required
                    data-placeholder="Selecciona un tipo de pago"
                >
                    @if($selectedTipoPago)
                        <option value="{{ $selectedTipoPago->id }}" selected>{{ $selectedTipoPago->nombre }}</option>
                    @endif
                </select>
                @error('tipo_pago_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="estado_pago_id" class="form-label">Estado de pago</label>
                <select
                    id="estado_pago_id"
                    name="estado_pago_id"
                    class="form-control @error('estado_pago_id') is-invalid @enderror js-select2-estado-pago"
                    required
                    data-placeholder="Selecciona un estado de pago"
                >
                    @if($selectedEstadoPago)
                        <option value="{{ $selectedEstadoPago->id }}" selected>{{ $selectedEstadoPago->nombre }}</option>
                    @endif
                </select>
                @error('estado_pago_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-4">
                <label for="concepto_pago_id" class="form-label">Concepto de pago</label>
                <select
                    id="concepto_pago_id"
                    name="concepto_pago_id"
                    class="form-control @error('concepto_pago_id') is-invalid @enderror"
                >
                    <option value="">— Sin concepto —</option>
                    @foreach($conceptosPago as $conceptoPago)
                        <option value="{{ $conceptoPago->id }}" @selected((string) old('concepto_pago_id', $pago->concepto_pago_id) === (string) $conceptoPago->id)>
                            {{ $conceptoPago->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('concepto_pago_id')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="importe" class="form-label">Importe</label>
                <input
                    type="number"
                    id="importe"
                    name="importe"
                    value="{{ old('importe', $pago->importe) }}"
                    class="form-control @error('importe') is-invalid @enderror"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    required
                    placeholder="Ejemplo: 49.90"
                >
                @error('importe')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6">
                <label for="fecha_pago" class="form-label">Fecha de pago</label>
                <input
                    type="datetime-local"
                    id="fecha_pago"
                    name="fecha_pago"
                    value="{{ old('fecha_pago', $pago->fecha_pago?->format('Y-m-d\\TH:i')) }}"
                    class="form-control @error('fecha_pago') is-invalid @enderror"
                >
                @error('fecha_pago')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="referencia_externa" class="form-label">Referencia externa</label>
                <input
                    type="text"
                    id="referencia_externa"
                    name="referencia_externa"
                    value="{{ old('referencia_externa', $pago->referencia_externa) }}"
                    class="form-control @error('referencia_externa') is-invalid @enderror"
                    maxlength="255"
                    placeholder="Ejemplo: PAY-2026-0001"
                >
                @error('referencia_externa')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-12">
                <label for="enlace_pago_externo" class="form-label">Enlace de pago externo</label>
                <input
                    type="url"
                    id="enlace_pago_externo"
                    name="enlace_pago_externo"
                    value="{{ old('enlace_pago_externo', $pago->enlace_pago_externo) }}"
                    class="form-control @error('enlace_pago_externo') is-invalid @enderror"
                    maxlength="1000"
                    placeholder="https://pay.example.com/checkout/..."
                >
                @error('enlace_pago_externo')
                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group col-lg-6 d-flex align-items-center">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="iniciado_por_bot" value="0">
                    <input
                        type="checkbox"
                        class="custom-control-input @error('iniciado_por_bot') is-invalid @enderror"
                        id="iniciado_por_bot"
                        name="iniciado_por_bot"
                        value="1"
                        @checked(old('iniciado_por_bot', $pago->iniciado_por_bot ?? false))
                    >
                    <label class="custom-control-label" for="iniciado_por_bot">Iniciado por bot</label>
                </div>
                @error('iniciado_por_bot')
                    <span class="invalid-feedback d-block ml-3" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.pagos.index') }}" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>

<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $numeroPersonas = trim((string) $this->input('numero_personas', ''));
        $precioCalculado = trim((string) $this->input('precio_calculado', ''));
        $precioTotal = trim((string) $this->input('precio_total', ''));
        $horaInicio = trim((string) $this->input('hora_inicio', ''));
        $horaFin = trim((string) $this->input('hora_fin', ''));
        $notas = trim((string) $this->input('notas', ''));
        $instruccionesLlegada = trim((string) $this->input('instrucciones_llegada', ''));
        $fechaEstimadaFin = trim((string) $this->input('fecha_estimada_fin', ''));
        $motivoCancelacion = trim((string) $this->input('motivo_cancelacion', ''));
        $canceladaPor = trim((string) $this->input('cancelada_por', ''));
        $horasMinCancelacion = trim((string) $this->input('horas_minimas_cancelacion', ''));
        $porcentajeSenal = trim((string) $this->input('porcentaje_senal', ''));

        $this->merge([
            'numero_personas' => $numeroPersonas !== '' ? $numeroPersonas : null,
            'precio_calculado' => $precioCalculado !== '' ? $this->normalizeDecimal($precioCalculado) : null,
            'precio_total' => $precioTotal !== '' ? $this->normalizeDecimal($precioTotal) : null,
            'hora_inicio' => $horaInicio !== '' ? $horaInicio : null,
            'hora_fin' => $horaFin !== '' ? $horaFin : null,
            'notas' => $notas !== '' ? $notas : null,
            'instrucciones_llegada' => $instruccionesLlegada !== '' ? $instruccionesLlegada : null,
            'fecha_estimada_fin' => $fechaEstimadaFin !== '' ? $fechaEstimadaFin : null,
            'documentacion_entregada' => $this->normalizeBoolean($this->input('documentacion_entregada')),
            'motivo_cancelacion' => $motivoCancelacion !== '' ? $motivoCancelacion : null,
            'cancelada_por' => $canceladaPor !== '' ? $canceladaPor : null,
            'horas_minimas_cancelacion' => $horasMinCancelacion !== '' ? $horasMinCancelacion : null,
            'porcentaje_senal' => $porcentajeSenal !== '' ? $this->normalizeDecimal($porcentajeSenal) : null,
            'permite_modificacion' => $this->normalizeNullableBoolean($this->input('permite_modificacion')),
            'es_reembolsable' => $this->normalizeNullableBoolean($this->input('es_reembolsable')),
        ]);
    }

    public function rules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'recurso_id' => ['required', 'integer', 'exists:recursos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'numero_personas' => ['nullable', 'integer', 'min:1'],
            'precio_calculado' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'precio_total' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'estado_reserva_id' => ['required', 'integer', 'exists:estados_reserva,id'],
            'notas' => ['nullable', 'string'],
            'instrucciones_llegada' => ['nullable', 'string'],
            'fecha_estimada_fin' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'documentacion_entregada' => ['required', 'boolean'],
            'motivo_cancelacion' => ['nullable', 'string'],
            'cancelada_por' => ['nullable', 'string', 'in:cliente,negocio,sistema'],
            'horas_minimas_cancelacion' => ['nullable', 'integer', 'min:0'],
            'permite_modificacion' => ['nullable', 'boolean'],
            'es_reembolsable' => ['nullable', 'boolean'],
            'porcentaje_senal' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if (array_key_exists('hora_inicio', $validated) && $validated['hora_inicio']) {
            $validated['hora_inicio'] = Carbon::createFromFormat('H:i', $validated['hora_inicio'])->format('H:i:s');
        }

        if (array_key_exists('hora_fin', $validated) && $validated['hora_fin']) {
            $validated['hora_fin'] = Carbon::createFromFormat('H:i', $validated['hora_fin'])->format('H:i:s');
        }

        if (array_key_exists('precio_calculado', $validated) && $validated['precio_calculado'] !== null) {
            $validated['precio_calculado'] = number_format((float) $validated['precio_calculado'], 2, '.', '');
        }

        if (array_key_exists('precio_total', $validated) && $validated['precio_total'] !== null) {
            $validated['precio_total'] = number_format((float) $validated['precio_total'], 2, '.', '');
        }

        if (array_key_exists('fecha_estimada_fin', $validated) && $validated['fecha_estimada_fin']) {
            $validated['fecha_estimada_fin'] = Carbon::createFromFormat('Y-m-d\TH:i', $validated['fecha_estimada_fin'])->format('Y-m-d H:i:s');
        }

        if (array_key_exists('porcentaje_senal', $validated) && $validated['porcentaje_senal'] !== null) {
            $validated['porcentaje_senal'] = number_format((float) $validated['porcentaje_senal'], 2, '.', '');
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'negocio_id.required' => 'Debes seleccionar un negocio.',
            'negocio_id.integer' => 'El negocio seleccionado no es válido.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'servicio_id.required' => 'Debes seleccionar un servicio.',
            'servicio_id.integer' => 'El servicio seleccionado no es válido.',
            'servicio_id.exists' => 'El servicio seleccionado no existe.',
            'recurso_id.required' => 'Debes seleccionar un recurso.',
            'recurso_id.integer' => 'El recurso seleccionado no es válido.',
            'recurso_id.exists' => 'El recurso seleccionado no existe.',
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.integer' => 'El cliente seleccionado no es válido.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener un formato válido.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.date_format' => 'La hora de fin debe tener un formato válido.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'numero_personas.integer' => 'El número de personas debe ser un número entero válido.',
            'numero_personas.min' => 'El número de personas debe ser al menos 1.',
            'precio_calculado.required' => 'El precio calculado es obligatorio.',
            'precio_calculado.numeric' => 'El precio calculado debe ser un número válido.',
            'precio_calculado.min' => 'El precio calculado no puede ser negativo.',
            'precio_calculado.max' => 'El precio calculado no puede superar 99999999.99.',
            'precio_total.numeric' => 'El precio total debe ser un número válido.',
            'precio_total.min' => 'El precio total no puede ser negativo.',
            'precio_total.max' => 'El precio total no puede superar 99999999.99.',
            'estado_reserva_id.required' => 'Debes seleccionar un estado de reserva.',
            'estado_reserva_id.integer' => 'El estado de reserva seleccionado no es válido.',
            'estado_reserva_id.exists' => 'El estado de reserva seleccionado no existe.',
            'notas.string' => 'Las notas deben ser un texto válido.',
            'fecha_estimada_fin.date_format' => 'La fecha estimada de fin debe tener un formato válido.',
            'documentacion_entregada.required' => 'Debes indicar si la documentación ha sido entregada.',
            'documentacion_entregada.boolean' => 'El valor de documentación entregada no es válido.',
            'cancelada_por.in' => 'El valor de cancelada por debe ser cliente, negocio o sistema.',
            'horas_minimas_cancelacion.integer' => 'Las horas mínimas de cancelación deben ser un número entero.',
            'horas_minimas_cancelacion.min' => 'Las horas mínimas de cancelación no pueden ser negativas.',
            'permite_modificacion.boolean' => 'El valor de permite modificación no es válido.',
            'es_reembolsable.boolean' => 'El valor de es reembolsable no es válido.',
            'porcentaje_senal.numeric' => 'El porcentaje de señal debe ser un número válido.',
            'porcentaje_senal.min' => 'El porcentaje de señal no puede ser negativo.',
            'porcentaje_senal.max' => 'El porcentaje de señal no puede superar el 100%.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    private function normalizeNullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    private function normalizeDecimal(string $value): string
    {
        $normalized = str_replace([' ', ','], ['', '.'], $value);

        return number_format((float) $normalized, 2, '.', '');
    }
}

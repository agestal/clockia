<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBloqueoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $motivo = trim((string) $this->input('motivo', ''));

        $this->merge([
            'hora_inicio' => $this->normalizeTimeForValidation($this->input('hora_inicio')),
            'hora_fin' => $this->normalizeTimeForValidation($this->input('hora_fin')),
            'motivo' => $motivo !== '' ? $motivo : null,
            'recurso_id' => $this->filled('recurso_id') ? (int) $this->input('recurso_id') : null,
            'negocio_id' => $this->filled('negocio_id') ? (int) $this->input('negocio_id') : null,
            'fecha' => $this->filled('fecha') ? $this->input('fecha') : null,
            'fecha_inicio' => $this->filled('fecha_inicio') ? $this->input('fecha_inicio') : null,
            'fecha_fin' => $this->filled('fecha_fin') ? $this->input('fecha_fin') : null,
            'dia_semana' => $this->filled('dia_semana') ? (int) $this->input('dia_semana') : null,
            'es_recurrente' => $this->normalizeBoolean($this->input('es_recurrente')),
            'activo' => $this->normalizeBoolean($this->input('activo', true)),
        ]);
    }

    public function rules(): array
    {
        return [
            'recurso_id' => ['nullable', 'integer', 'exists:recursos,id'],
            'negocio_id' => ['nullable', 'integer', 'exists:negocios,id'],
            'tipo_bloqueo_id' => ['required', 'integer', 'exists:tipos_bloqueo,id'],
            'fecha' => ['nullable', 'date'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'es_recurrente' => ['required', 'boolean'],
            'dia_semana' => ['nullable', 'integer', 'in:0,1,2,3,4,5,6'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $horaInicio = $this->input('hora_inicio');
            $horaFin = $this->input('hora_fin');

            if (($horaInicio === null && $horaFin !== null) || ($horaInicio !== null && $horaFin === null)) {
                $validator->errors()->add('hora_fin', 'Debes informar ambas horas o dejar ambas vacías para un bloqueo de día completo.');
                return;
            }

            if ($horaInicio !== null && $horaFin !== null && $horaFin <= $horaInicio) {
                $validator->errors()->add('hora_fin', 'La hora de fin debe ser posterior a la hora de inicio.');
            }

            $esRecurrente = (bool) $this->input('es_recurrente');
            $fecha = $this->input('fecha');
            $fechaInicio = $this->input('fecha_inicio');
            $fechaFin = $this->input('fecha_fin');
            $diaSemana = $this->input('dia_semana');

            if ($esRecurrente) {
                if ($diaSemana === null) {
                    $validator->errors()->add('dia_semana', 'Si el bloqueo es recurrente, debes indicar el día de la semana.');
                }
            } else {
                $tieneRango = $fechaInicio !== null && $fechaFin !== null;
                $tienePuntual = $fecha !== null;

                if (! $tieneRango && ! $tienePuntual) {
                    $validator->errors()->add('fecha', 'Debes indicar una fecha puntual o un rango de fechas.');
                }

                if ($fechaInicio !== null && $fechaFin === null) {
                    $validator->errors()->add('fecha_fin', 'Si indicas fecha de inicio, debes indicar también la fecha de fin.');
                }

                if ($fechaFin !== null && $fechaInicio === null) {
                    $validator->errors()->add('fecha_inicio', 'Si indicas fecha de fin, debes indicar también la fecha de inicio.');
                }
            }

            $recursoId = $this->input('recurso_id');
            $negocioId = $this->input('negocio_id');

            if ($recursoId === null && $negocioId === null) {
                $validator->errors()->add('recurso_id', 'Debes indicar un recurso o un negocio al que aplicar el bloqueo.');
            }
        });
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $validated['hora_inicio'] = $validated['hora_inicio'] !== null ? $this->normalizeTimeForStorage($validated['hora_inicio']) : null;
        $validated['hora_fin'] = $validated['hora_fin'] !== null ? $this->normalizeTimeForStorage($validated['hora_fin']) : null;

        return $validated;
    }

    public function messages(): array
    {
        return [
            'recurso_id.integer' => 'El recurso seleccionado no es válido.',
            'recurso_id.exists' => 'El recurso seleccionado no existe.',
            'negocio_id.integer' => 'El negocio seleccionado no es válido.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'tipo_bloqueo_id.required' => 'Debes seleccionar un tipo de bloqueo.',
            'tipo_bloqueo_id.integer' => 'El tipo de bloqueo seleccionado no es válido.',
            'tipo_bloqueo_id.exists' => 'El tipo de bloqueo seleccionado no existe.',
            'fecha.date' => 'La fecha indicada no es válida.',
            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'fecha_fin.date' => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'es_recurrente.required' => 'Debes indicar si el bloqueo es recurrente.',
            'es_recurrente.boolean' => 'El valor de recurrente no es válido.',
            'dia_semana.integer' => 'El día de la semana no es válido.',
            'dia_semana.in' => 'El día de la semana no es válido.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'motivo.string' => 'El motivo debe ser un texto válido.',
            'motivo.max' => 'El motivo no puede superar los 255 caracteres.',
            'activo.required' => 'Debes indicar si el bloqueo está activo.',
            'activo.boolean' => 'El estado del bloqueo no es válido.',
        ];
    }

    private function normalizeTimeForValidation(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
    }

    private function normalizeTimeForStorage(string $value): string
    {
        return $value . ':00';
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

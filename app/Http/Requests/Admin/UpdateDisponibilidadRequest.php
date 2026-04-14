<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisponibilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombreTurno = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre_turno', '')));
        $bufferMinutos = trim((string) $this->input('buffer_minutos', ''));

        $this->merge([
            'activo' => $this->normalizeBoolean($this->input('activo')),
            'hora_inicio' => $this->normalizeTimeForValidation($this->input('hora_inicio')),
            'hora_fin' => $this->normalizeTimeForValidation($this->input('hora_fin')),
            'nombre_turno' => $nombreTurno !== '' ? $nombreTurno : null,
            'buffer_minutos' => $bufferMinutos !== '' ? $bufferMinutos : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'recurso_id' => ['required', 'integer', 'exists:recursos,id'],
            'dia_semana' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'activo' => ['required', 'boolean'],
            'nombre_turno' => ['nullable', 'string', 'max:255'],
            'buffer_minutos' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $validated['hora_inicio'] = $this->normalizeTimeForStorage($validated['hora_inicio']);
        $validated['hora_fin'] = $this->normalizeTimeForStorage($validated['hora_fin']);

        return $validated;
    }

    public function messages(): array
    {
        return [
            'recurso_id.required' => 'Debes seleccionar un recurso.',
            'recurso_id.integer' => 'El recurso seleccionado no es válido.',
            'recurso_id.exists' => 'El recurso seleccionado no existe.',
            'dia_semana.required' => 'Debes seleccionar un día de la semana.',
            'dia_semana.integer' => 'El día de la semana no es válido.',
            'dia_semana.in' => 'El día de la semana seleccionado no es válido.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'activo.required' => 'Debes indicar si la disponibilidad está activa.',
            'activo.boolean' => 'El estado de la disponibilidad no es válido.',
            'nombre_turno.max' => 'El nombre del turno no puede superar los 255 caracteres.',
            'buffer_minutos.integer' => 'Los minutos de buffer deben ser un número entero válido.',
            'buffer_minutos.min' => 'Los minutos de buffer no pueden ser negativos.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
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
}

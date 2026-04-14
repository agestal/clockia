<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOcupacionExternaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $titulo = trim((string) $this->input('titulo', ''));
        $descripcion = trim((string) $this->input('descripcion', ''));
        $externalId = trim((string) $this->input('external_id', ''));

        $this->merge([
            'titulo' => $titulo !== '' ? $titulo : null,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'external_id' => $externalId !== '' ? $externalId : null,
            'hora_inicio' => $this->normalizeTimeForValidation($this->input('hora_inicio')),
            'hora_fin' => $this->normalizeTimeForValidation($this->input('hora_fin')),
            'negocio_id' => $this->filled('negocio_id') ? (int) $this->input('negocio_id') : null,
            'integracion_id' => $this->filled('integracion_id') ? (int) $this->input('integracion_id') : null,
            'integracion_mapeo_id' => $this->filled('integracion_mapeo_id') ? (int) $this->input('integracion_mapeo_id') : null,
            'recurso_id' => $this->filled('recurso_id') ? (int) $this->input('recurso_id') : null,
            'fecha' => $this->filled('fecha') ? $this->input('fecha') : null,
            'es_dia_completo' => $this->normalizeBoolean($this->input('es_dia_completo')),
        ]);
    }

    public function rules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'integracion_id' => ['nullable', 'integer', 'exists:integraciones,id'],
            'integracion_mapeo_id' => ['nullable', 'integer', 'exists:integracion_mapeos,id'],
            'recurso_id' => ['nullable', 'integer', 'exists:recursos,id'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'external_id' => ['required', 'string', 'max:255'],
            'external_calendar_id' => ['nullable', 'string', 'max:255'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['nullable', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'inicio_datetime' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'fin_datetime' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'es_dia_completo' => ['required', 'boolean'],
            'origen' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $esDiaCompleto = (bool) $this->input('es_dia_completo');
            $horaInicio = $this->input('hora_inicio');
            $horaFin = $this->input('hora_fin');

            if (! $esDiaCompleto && $horaInicio !== null && $horaFin !== null && $horaFin <= $horaInicio) {
                $validator->errors()->add('hora_fin', 'La hora de fin debe ser posterior a la hora de inicio.');
            }

            $inicioDatetime = $this->input('inicio_datetime');
            $finDatetime = $this->input('fin_datetime');

            if ($inicioDatetime !== null && $finDatetime !== null && $finDatetime < $inicioDatetime) {
                $validator->errors()->add('fin_datetime', 'La fecha/hora de fin debe ser igual o posterior a la de inicio.');
            }

            $fecha = $this->input('fecha');

            if ($fecha === null && $inicioDatetime === null) {
                $validator->errors()->add('fecha', 'Debes indicar al menos una fecha o un inicio datetime.');
            }
        });
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        $validated['hora_inicio'] = isset($validated['hora_inicio']) && $validated['hora_inicio'] !== null
            ? $this->normalizeTimeForStorage($validated['hora_inicio'])
            : null;

        $validated['hora_fin'] = isset($validated['hora_fin']) && $validated['hora_fin'] !== null
            ? $this->normalizeTimeForStorage($validated['hora_fin'])
            : null;

        if (! empty($validated['inicio_datetime'])) {
            $validated['inicio_datetime'] = Carbon::createFromFormat('Y-m-d\TH:i', $validated['inicio_datetime'])->format('Y-m-d H:i:s');
        }

        if (! empty($validated['fin_datetime'])) {
            $validated['fin_datetime'] = Carbon::createFromFormat('Y-m-d\TH:i', $validated['fin_datetime'])->format('Y-m-d H:i:s');
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'negocio_id.required' => 'Debes seleccionar un negocio.',
            'negocio_id.integer' => 'El negocio seleccionado no es válido.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'integracion_id.integer' => 'La integración seleccionada no es válida.',
            'integracion_id.exists' => 'La integración seleccionada no existe.',
            'integracion_mapeo_id.integer' => 'El mapeo de integración seleccionado no es válido.',
            'integracion_mapeo_id.exists' => 'El mapeo de integración seleccionado no existe.',
            'recurso_id.integer' => 'El recurso seleccionado no es válido.',
            'recurso_id.exists' => 'El recurso seleccionado no existe.',
            'proveedor.string' => 'El proveedor debe ser un texto válido.',
            'proveedor.max' => 'El proveedor no puede superar los 255 caracteres.',
            'external_id.required' => 'El ID externo es obligatorio.',
            'external_id.string' => 'El ID externo debe ser un texto válido.',
            'external_id.max' => 'El ID externo no puede superar los 255 caracteres.',
            'external_calendar_id.string' => 'El ID de calendario externo debe ser un texto válido.',
            'external_calendar_id.max' => 'El ID de calendario externo no puede superar los 255 caracteres.',
            'titulo.string' => 'El título debe ser un texto válido.',
            'titulo.max' => 'El título no puede superar los 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'fecha.date' => 'La fecha indicada no es válida.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'inicio_datetime.date_format' => 'El inicio datetime debe tener formato AAAA-MM-DD HH:MM.',
            'fin_datetime.date_format' => 'El fin datetime debe tener formato AAAA-MM-DD HH:MM.',
            'es_dia_completo.required' => 'Debes indicar si es día completo.',
            'es_dia_completo.boolean' => 'El valor de día completo no es válido.',
            'origen.string' => 'El origen debe ser un texto válido.',
            'origen.max' => 'El origen no puede superar los 255 caracteres.',
            'estado.string' => 'El estado debe ser un texto válido.',
            'estado.max' => 'El estado no puede superar los 255 caracteres.',
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

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegracionMapeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'external_id' => trim((string) $this->input('external_id', '')),
            'external_parent_id' => trim((string) $this->input('external_parent_id', '')) ?: null,
            'nombre_externo' => trim((string) $this->input('nombre_externo', '')) ?: null,
            'negocio_id' => $this->input('negocio_id') ?: null,
            'recurso_id' => $this->input('recurso_id') ?: null,
            'servicio_id' => $this->input('servicio_id') ?: null,
            'configuracion' => trim((string) $this->input('configuracion', '')) ?: null,
            'activo' => $this->normalizeBoolean($this->input('activo')),
        ]);
    }

    public function rules(): array
    {
        return [
            'integracion_id' => ['required', 'integer', 'exists:integraciones,id'],
            'tipo_origen' => ['required', 'string', 'max:255'],
            'external_id' => ['required', 'string', 'max:255'],
            'external_parent_id' => ['nullable', 'string', 'max:255'],
            'nombre_externo' => ['nullable', 'string', 'max:255'],
            'negocio_id' => ['nullable', 'integer', 'exists:negocios,id'],
            'recurso_id' => ['nullable', 'integer', 'exists:recursos,id'],
            'servicio_id' => ['nullable', 'integer', 'exists:servicios,id'],
            'configuracion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $negocioId = $this->input('negocio_id');
            $recursoId = $this->input('recurso_id');
            $servicioId = $this->input('servicio_id');

            if (empty($negocioId) && empty($recursoId) && empty($servicioId)) {
                $validator->errors()->add(
                    'negocio_id',
                    'Debes seleccionar al menos un destino: negocio, recurso o servicio.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'integracion_id.required' => 'La integración es obligatoria.',
            'integracion_id.integer' => 'La integración debe ser un valor numérico.',
            'integracion_id.exists' => 'La integración seleccionada no existe.',
            'tipo_origen.required' => 'El tipo de origen es obligatorio.',
            'tipo_origen.string' => 'El tipo de origen debe ser un texto válido.',
            'tipo_origen.max' => 'El tipo de origen no puede superar los 255 caracteres.',
            'external_id.required' => 'El ID externo es obligatorio.',
            'external_id.string' => 'El ID externo debe ser un texto válido.',
            'external_id.max' => 'El ID externo no puede superar los 255 caracteres.',
            'external_parent_id.string' => 'El ID padre externo debe ser un texto válido.',
            'external_parent_id.max' => 'El ID padre externo no puede superar los 255 caracteres.',
            'nombre_externo.string' => 'El nombre externo debe ser un texto válido.',
            'nombre_externo.max' => 'El nombre externo no puede superar los 255 caracteres.',
            'negocio_id.integer' => 'El negocio debe ser un valor numérico.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'recurso_id.integer' => 'El recurso debe ser un valor numérico.',
            'recurso_id.exists' => 'El recurso seleccionado no existe.',
            'servicio_id.integer' => 'El servicio debe ser un valor numérico.',
            'servicio_id.exists' => 'El servicio seleccionado no existe.',
            'configuracion.string' => 'La configuración debe ser un texto válido.',
            'activo.required' => 'El estado activo es obligatorio.',
            'activo.boolean' => 'El valor de activo no es válido.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

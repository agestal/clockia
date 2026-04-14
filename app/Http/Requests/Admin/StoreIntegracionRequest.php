<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', '')));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'activo' => filter_var($this->input('activo'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'proveedor' => ['required', 'string', 'in:google_calendar'],
            'nombre' => ['required', 'string', 'min:2', 'max:255'],
            'modo_operacion' => ['required', 'string', 'in:solo_clockia,coexistencia,migracion'],
            'estado' => ['required', 'string', 'in:pendiente,conectada,error,desactivada'],
            'configuracion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key !== null) {
            return $data;
        }

        if (isset($data['configuracion']) && $data['configuracion'] !== null && $data['configuracion'] !== '') {
            $decoded = json_decode($data['configuracion'], true);
            $data['configuracion'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        } else {
            $data['configuracion'] = null;
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'negocio_id.required' => 'El negocio es obligatorio.',
            'negocio_id.integer' => 'El negocio debe ser un valor válido.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'proveedor.required' => 'El proveedor es obligatorio.',
            'proveedor.string' => 'El proveedor debe ser un texto válido.',
            'proveedor.in' => 'El proveedor seleccionado no es válido.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'modo_operacion.required' => 'El modo de operación es obligatorio.',
            'modo_operacion.string' => 'El modo de operación debe ser un texto válido.',
            'modo_operacion.in' => 'El modo de operación seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.string' => 'El estado debe ser un texto válido.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'configuracion.string' => 'La configuración debe ser un texto válido.',
            'activo.required' => 'El campo activo es obligatorio.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}

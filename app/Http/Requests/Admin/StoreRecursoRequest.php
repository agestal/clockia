<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', '')));
        $capacidad = trim((string) $this->input('capacidad', ''));
        $capacidadMinima = trim((string) $this->input('capacidad_minima', ''));
        $notasPublicas = trim((string) $this->input('notas_publicas', ''));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'capacidad' => $capacidad !== '' ? $capacidad : null,
            'activo' => $this->normalizeBoolean($this->input('activo')),
            'capacidad_minima' => $capacidadMinima !== '' ? $capacidadMinima : null,
            'combinable' => $this->normalizeBoolean($this->input('combinable')),
            'notas_publicas' => $notasPublicas !== '' ? $notasPublicas : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = (string) $value;

                    if (! preg_match('/\p{L}/u', $value)) {
                        $fail('El nombre debe contener al menos una letra.');
                        return;
                    }

                    if (preg_match('/^\d+$/', $value)) {
                        $fail('El nombre no puede estar formado solo por números.');
                    }
                },
            ],
            'tipo_recurso_id' => ['required', 'integer', 'exists:tipos_recurso,id'],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'activo' => ['required', 'boolean'],
            'capacidad_minima' => [
                'nullable',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $capacidad = $this->input('capacidad');
                    if ($value !== null && $capacidad !== null && (int) $value > (int) $capacidad) {
                        $fail('La capacidad mínima no puede ser mayor que la capacidad máxima del recurso.');
                    }
                },
            ],
            'combinable' => ['required', 'boolean'],
            'notas_publicas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'negocio_id.required' => 'Debes seleccionar un negocio.',
            'negocio_id.integer' => 'El negocio seleccionado no es válido.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'tipo_recurso_id.required' => 'Debes seleccionar un tipo de recurso.',
            'tipo_recurso_id.integer' => 'El tipo de recurso seleccionado no es válido.',
            'tipo_recurso_id.exists' => 'El tipo de recurso seleccionado no existe.',
            'capacidad.integer' => 'La capacidad debe ser un número entero válido.',
            'capacidad.min' => 'La capacidad debe ser al menos 1.',
            'activo.required' => 'Debes indicar si el recurso está activo.',
            'activo.boolean' => 'El estado del recurso no es válido.',
            'capacidad_minima.integer' => 'La capacidad mínima debe ser un número entero válido.',
            'capacidad_minima.min' => 'La capacidad mínima debe ser al menos 1.',
            'combinable.required' => 'Debes indicar si el recurso es combinable.',
            'combinable.boolean' => 'El valor de combinable no es válido.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

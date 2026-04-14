<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', '')));
        $email = preg_replace('/\s+/u', ' ', trim((string) $this->input('email', '')));
        $telefono = preg_replace('/\s+/u', ' ', trim((string) $this->input('telefono', '')));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'email' => $email !== '' ? $email : null,
            'telefono' => $telefono !== '' ? $telefono : null,
            'activo' => $this->normalizeBoolean($this->input('activo')),
        ]);
    }

    public function rules(): array
    {
        return [
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
            'tipo_negocio_id' => ['required', 'integer', 'exists:tipos_negocio,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'zona_horaria' => ['required', 'string', 'timezone'],
            'activo' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'tipo_negocio_id.required' => 'Debes seleccionar un tipo de negocio.',
            'tipo_negocio_id.integer' => 'El tipo de negocio seleccionado no es válido.',
            'tipo_negocio_id.exists' => 'El tipo de negocio seleccionado no existe.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede superar los 255 caracteres.',
            'telefono.string' => 'El teléfono debe ser un texto válido.',
            'telefono.max' => 'El teléfono no puede superar los 255 caracteres.',
            'zona_horaria.required' => 'La zona horaria es obligatoria.',
            'zona_horaria.string' => 'La zona horaria debe ser un texto válido.',
            'zona_horaria.timezone' => 'La zona horaria seleccionada no es válida.',
            'activo.required' => 'Debes indicar si el negocio está activo.',
            'activo.boolean' => 'El estado del negocio no es válido.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

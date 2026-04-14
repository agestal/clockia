<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoRecursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', '')));
        $descripcion = preg_replace('/\s+/u', ' ', trim((string) $this->input('descripcion', '')));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
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
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
        ];
    }
}

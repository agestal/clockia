<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
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
        $notas = trim((string) $this->input('notas', ''));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'email' => $email !== '' ? $email : null,
            'telefono' => $telefono !== '' ? $telefono : null,
            'notas' => $notas !== '' ? $notas : null,
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
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede superar los 255 caracteres.',
            'telefono.string' => 'El teléfono debe ser un texto válido.',
            'telefono.max' => 'El teléfono no puede superar los 255 caracteres.',
            'notas.string' => 'Las notas deben ser un texto válido.',
        ];
    }
}

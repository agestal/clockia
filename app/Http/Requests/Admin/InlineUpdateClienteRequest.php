<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InlineUpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $field = (string) $this->input('field');
        $value = preg_replace('/\s+/u', ' ', trim((string) $this->input('value', '')));

        $this->merge([
            'field' => $field,
            'value' => $value !== '' ? $value : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:nombre,telefono'],
            'value' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $field = (string) $this->input('field');

                    if ($field === 'nombre') {
                        $value = (string) $value;

                        if ($value === '') {
                            $fail('El nombre es obligatorio.');
                            return;
                        }

                        if (mb_strlen($value) < 2) {
                            $fail('El nombre debe tener al menos 2 caracteres.');
                            return;
                        }

                        if (! preg_match('/\p{L}/u', $value)) {
                            $fail('El nombre debe contener al menos una letra.');
                            return;
                        }

                        if (preg_match('/^\d+$/', $value)) {
                            $fail('El nombre no puede estar formado solo por números.');
                        }
                    }
                },
            ],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            $validated['field'] => $validated['value'],
        ];
    }

    public function messages(): array
    {
        return [
            'field.required' => 'El campo a actualizar es obligatorio.',
            'field.in' => 'El campo solicitado no se puede editar en línea.',
            'value.string' => 'El valor debe ser un texto válido.',
            'value.max' => 'El valor no puede superar los 255 caracteres.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InlineUpdateRecursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $field = (string) $this->input('field');
        $value = $this->input('value');

        if ($field === 'capacidad') {
            $value = trim((string) $value);
            $value = $value !== '' ? $value : null;
        }

        if (in_array($field, ['activo', 'combinable'], true)) {
            $value = $this->normalizeBoolean($value);
        }

        $this->merge([
            'field' => $field,
            'value' => $value,
        ]);
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:capacidad,activo,combinable'],
            'value' => [
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $field = (string) $this->input('field');

                    if ($field === 'capacidad') {
                        if ($value === null) {
                            return;
                        }

                        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $fail('La capacidad debe ser un número entero válido.');
                            return;
                        }

                        if ((int) $value < 1) {
                            $fail('La capacidad debe ser al menos 1.');
                        }

                        return;
                    }

                    if ($field === 'activo' && ! is_bool($value)) {
                        $fail('El estado del recurso no es válido.');
                    }

                    if ($field === 'combinable' && ! is_bool($value)) {
                        $fail('El valor de combinable no es válido.');
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
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

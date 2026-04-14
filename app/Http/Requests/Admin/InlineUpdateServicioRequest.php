<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InlineUpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $field = (string) $this->input('field');
        $value = $this->input('value');

        if ($field === 'precio_base') {
            $value = trim((string) $value);
            $value = $value !== '' ? $this->normalizeDecimal($value) : null;
        }

        if (in_array($field, ['requiere_pago', 'activo'], true)) {
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
            'field' => ['required', 'string', 'in:precio_base,requiere_pago,activo'],
            'value' => [
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $field = (string) $this->input('field');

                    if ($field === 'precio_base') {
                        if ($value === null || $value === '') {
                            $fail('El precio base es obligatorio.');
                            return;
                        }

                        if (! is_numeric($value)) {
                            $fail('El precio base debe ser un número válido.');
                            return;
                        }

                        if ((float) $value < 0) {
                            $fail('El precio base no puede ser negativo.');
                            return;
                        }

                        if ((float) $value > 99999999.99) {
                            $fail('El precio base no puede superar 99999999.99.');
                        }

                        return;
                    }

                    if (in_array($field, ['requiere_pago', 'activo'], true) && ! is_bool($value)) {
                        $fail('El valor indicado no es válido.');
                    }
                },
            ],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if ($validated['field'] === 'precio_base') {
            $validated['value'] = number_format((float) $validated['value'], 2, '.', '');
        }

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

    private function normalizeDecimal(string $value): string
    {
        $normalized = str_replace([' ', ','], ['', '.'], $value);

        return number_format((float) $normalized, 2, '.', '');
    }
}

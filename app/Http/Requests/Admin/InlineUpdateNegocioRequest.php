<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InlineUpdateNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'field' => (string) $this->input('field'),
            'value' => $this->normalizeBoolean($this->input('value')),
        ]);
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:activo'],
            'value' => ['required', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'activo' => $validated['value'],
        ];
    }

    public function messages(): array
    {
        return [
            'field.required' => 'El campo a actualizar es obligatorio.',
            'field.in' => 'El campo solicitado no se puede editar en línea.',
            'value.required' => 'Debes indicar si el negocio está activo.',
            'value.boolean' => 'El estado del negocio no es válido.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

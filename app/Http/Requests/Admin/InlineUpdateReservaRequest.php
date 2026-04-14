<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InlineUpdateReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'field' => (string) $this->input('field'),
            'value' => $this->input('value'),
        ]);
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:estado_reserva_id'],
            'value' => ['required', 'integer', 'exists:estados_reserva,id'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'estado_reserva_id' => $validated['value'],
        ];
    }

    public function messages(): array
    {
        return [
            'field.required' => 'El campo a actualizar es obligatorio.',
            'field.in' => 'El campo solicitado no se puede editar en línea.',
            'value.required' => 'Debes seleccionar un estado de reserva.',
            'value.integer' => 'El estado de reserva seleccionado no es válido.',
            'value.exists' => 'El estado de reserva seleccionado no existe.',
        ];
    }
}

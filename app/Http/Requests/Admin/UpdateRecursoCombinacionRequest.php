<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecursoCombinacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recurso_id' => ['required', 'integer', 'exists:recursos,id'],
            'recurso_combinado_id' => [
                'required',
                'integer',
                'exists:recursos,id',
                'different:recurso_id',
                Rule::unique('recurso_combinaciones')->where(function ($query) {
                    return $query->where('recurso_id', $this->input('recurso_id'));
                })->ignore($this->route('recurso_combinacion')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recurso_id.required' => 'Debes seleccionar un recurso.',
            'recurso_id.integer' => 'El recurso seleccionado no es válido.',
            'recurso_id.exists' => 'El recurso seleccionado no existe.',
            'recurso_combinado_id.required' => 'Debes seleccionar un recurso combinado.',
            'recurso_combinado_id.integer' => 'El recurso combinado seleccionado no es válido.',
            'recurso_combinado_id.exists' => 'El recurso combinado seleccionado no existe.',
            'recurso_combinado_id.different' => 'El recurso combinado no puede ser el mismo que el recurso principal.',
            'recurso_combinado_id.unique' => 'Esta combinación de recursos ya existe.',
        ];
    }
}

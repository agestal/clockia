<?php

namespace App\Http\Requests\Api\V1\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort' => (string) $this->input('sort', 'name'),
            'filter' => [
                'name' => $this->filled('filter.name') ? trim((string) $this->input('filter.name')) : null,
                'is_active' => $this->input('filter.is_active'),
                'resource_type_id' => $this->filled('filter.resource_type_id') ? trim((string) $this->input('filter.resource_type_id')) : null,
            ],
            'page' => [
                'size' => $this->input('page.size', 15),
                'number' => $this->input('page.number', 1),
            ],
        ]);
    }

    public function rules(): array
    {
        return [
            'sort' => ['nullable', 'string', Rule::in(['name', '-name', 'created_at', '-created_at'])],
            'filter.name' => ['nullable', 'string', 'max:255'],
            'filter.is_active' => ['nullable', 'boolean'],
            'filter.resource_type_id' => ['nullable', 'integer', 'exists:tipos_recurso,id'],
            'page.size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page.number' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\Blocks;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort' => (string) $this->input('sort', '-date'),
            'filter' => [
                'resource_id' => $this->filled('filter.resource_id') ? trim((string) $this->input('filter.resource_id')) : null,
                'service_id' => $this->filled('filter.service_id') ? trim((string) $this->input('filter.service_id')) : null,
                'date' => $this->filled('filter.date') ? trim((string) $this->input('filter.date')) : null,
                'block_type_id' => $this->filled('filter.block_type_id') ? trim((string) $this->input('filter.block_type_id')) : null,
            ],
            'page' => [
                'size' => $this->input('page.size', 15),
                'number' => $this->input('page.number', 1),
            ],
        ]);
    }

    public function rules(): array
    {
        $business = $this->route('business');
        $businessId = $business instanceof Negocio ? $business->getKey() : null;

        return [
            'sort' => ['nullable', 'string', Rule::in(['date', '-date', 'start_time', '-start_time', 'created_at', '-created_at'])],
            'filter.resource_id' => [
                'nullable',
                'integer',
                Rule::exists('recursos', 'id')->where(fn ($query) => $query->where('negocio_id', $businessId)),
            ],
            'filter.service_id' => [
                'nullable',
                'integer',
                Rule::exists('servicios', 'id')->where(fn ($query) => $query->where('negocio_id', $businessId)),
            ],
            'filter.date' => ['nullable', 'date'],
            'filter.block_type_id' => ['nullable', 'integer', 'exists:tipos_bloqueo,id'],
            'page.size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page.number' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

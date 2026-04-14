<?php

namespace App\Http\Requests\Api\V1\Availabilities;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort' => (string) $this->input('sort', 'weekday'),
            'filter' => [
                'resource_id' => $this->filled('filter.resource_id') ? trim((string) $this->input('filter.resource_id')) : null,
                'weekday' => $this->filled('filter.weekday') ? trim((string) $this->input('filter.weekday')) : null,
                'is_active' => $this->input('filter.is_active'),
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
            'sort' => ['nullable', 'string', Rule::in(['weekday', '-weekday', 'start_time', '-start_time', 'created_at', '-created_at'])],
            'filter.resource_id' => [
                'nullable',
                'integer',
                Rule::exists('recursos', 'id')->where(fn ($query) => $query->where('negocio_id', $businessId)),
            ],
            'filter.weekday' => ['nullable', 'integer', 'in:0,1,2,3,4,5,6'],
            'filter.is_active' => ['nullable', 'boolean'],
            'page.size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page.number' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\Availabilities;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $shiftName = trim((string) $this->input('shiftName', ''));
        $bufferMinutes = trim((string) $this->input('bufferMinutes', ''));

        $this->merge([
            'resource_id' => $this->filled('resource_id') ? trim((string) $this->input('resource_id')) : null,
            'weekday' => $this->filled('weekday') ? trim((string) $this->input('weekday')) : null,
            'start_time' => $this->normalizeTimeForValidation($this->input('start_time')),
            'end_time' => $this->normalizeTimeForValidation($this->input('end_time')),
            'is_active' => $this->has('is_active')
                ? $this->normalizeBoolean($this->input('is_active'))
                : null,
            'shiftName' => $shiftName !== '' ? $shiftName : null,
            'bufferMinutes' => $bufferMinutes !== '' ? $bufferMinutes : null,
        ]);
    }

    public function rules(): array
    {
        $business = $this->route('business');
        $businessId = $business instanceof Negocio ? $business->getKey() : null;

        return [
            'resource_id' => [
                'required',
                'integer',
                Rule::exists('recursos', 'id')->where(fn ($query) => $query->where('negocio_id', $businessId)),
            ],
            'weekday' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['required', 'boolean'],
            'shiftName' => ['nullable', 'string', 'max:255'],
            'bufferMinutes' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function availabilityAttributes(): array
    {
        $validated = $this->validated();

        return [
            'recurso_id' => (int) $validated['resource_id'],
            'dia_semana' => (int) $validated['weekday'],
            'hora_inicio' => $this->normalizeTimeForStorage($validated['start_time']),
            'hora_fin' => $this->normalizeTimeForStorage($validated['end_time']),
            'activo' => (bool) $validated['is_active'],
            'nombre_turno' => $validated['shiftName'] ?? null,
            'buffer_minutos' => $validated['bufferMinutes'] ?? null,
        ];
    }

    public function messages(): array
    {
        return [
            'resource_id.required' => 'The resource_id field is required.',
            'resource_id.integer' => 'The selected resource_id is invalid.',
            'resource_id.exists' => 'The selected resource does not belong to this business.',
            'weekday.required' => 'The weekday field is required.',
            'weekday.integer' => 'The weekday field must be an integer.',
            'weekday.in' => 'The selected weekday is invalid.',
            'start_time.required' => 'The start_time field is required.',
            'start_time.date_format' => 'The start_time must match the format H:i.',
            'end_time.required' => 'The end_time field is required.',
            'end_time.date_format' => 'The end_time must match the format H:i.',
            'end_time.after' => 'The end_time must be a time after start_time.',
            'is_active.required' => 'The is_active field is required.',
            'is_active.boolean' => 'The is_active field must be true or false.',
            'shiftName.max' => 'The shift name cannot exceed 255 characters.',
            'bufferMinutes.integer' => 'The buffer minutes must be an integer.',
            'bufferMinutes.min' => 'The buffer minutes cannot be negative.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    private function normalizeTimeForValidation(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
    }

    private function normalizeTimeForStorage(string $value): string
    {
        return $value.':00';
    }
}

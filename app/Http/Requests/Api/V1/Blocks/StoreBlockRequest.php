<?php

namespace App\Http\Requests\Api\V1\Blocks;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $reason = trim((string) $this->input('reason', ''));

        $this->merge([
            'resource_id' => $this->filled('resource_id') ? trim((string) $this->input('resource_id')) : null,
            'block_type_id' => $this->filled('block_type_id') ? trim((string) $this->input('block_type_id')) : null,
            'start_time' => $this->normalizeTimeForValidation($this->input('start_time')),
            'end_time' => $this->normalizeTimeForValidation($this->input('end_time')),
            'reason' => $reason !== '' ? $reason : null,
            'start_date' => $this->filled('start_date') ? $this->input('start_date') : null,
            'end_date' => $this->filled('end_date') ? $this->input('end_date') : null,
            'is_recurring' => $this->normalizeBoolean($this->input('is_recurring', false)),
            'weekday' => $this->filled('weekday') ? (int) $this->input('weekday') : null,
            'is_active' => $this->normalizeBoolean($this->input('is_active', true)),
        ]);
    }

    public function rules(): array
    {
        $business = $this->route('business');
        $businessId = $business instanceof Negocio ? $business->getKey() : null;

        return [
            'resource_id' => [
                'nullable',
                'integer',
                Rule::exists('recursos', 'id')->where(fn ($query) => $query->where('negocio_id', $businessId)),
            ],
            'block_type_id' => ['required', 'integer', 'exists:tipos_bloqueo,id'],
            'date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_recurring' => ['required', 'boolean'],
            'weekday' => ['nullable', 'integer', 'in:0,1,2,3,4,5,6'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if (($startTime === null && $endTime !== null) || ($startTime !== null && $endTime === null)) {
                $validator->errors()->add('end_time', 'You must provide both start_time and end_time or leave both empty for a full-day block.');

                return;
            }

            if ($startTime !== null && $endTime !== null && $endTime <= $startTime) {
                $validator->errors()->add('end_time', 'The end_time must be a time after start_time.');
            }

            $isRecurring = (bool) $this->input('is_recurring');
            $date = $this->input('date');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $weekday = $this->input('weekday');

            if ($isRecurring) {
                if ($weekday === null) {
                    $validator->errors()->add('weekday', 'The weekday field is required when is_recurring is true.');
                }
            } else {
                $hasRange = $startDate !== null && $endDate !== null;
                $hasPunctual = $date !== null;

                if (! $hasRange && ! $hasPunctual) {
                    $validator->errors()->add('date', 'You must provide either a punctual date or a date range.');
                }

                if ($startDate !== null && $endDate === null) {
                    $validator->errors()->add('end_date', 'You must provide end_date when start_date is set.');
                }

                if ($endDate !== null && $startDate === null) {
                    $validator->errors()->add('start_date', 'You must provide start_date when end_date is set.');
                }
            }
        });
    }

    public function blockAttributes(): array
    {
        $validated = $this->validated();
        $business = $this->route('business');
        $businessId = $business instanceof Negocio ? $business->getKey() : null;

        return [
            'negocio_id' => $businessId,
            'recurso_id' => isset($validated['resource_id']) ? (int) $validated['resource_id'] : null,
            'tipo_bloqueo_id' => (int) $validated['block_type_id'],
            'fecha' => $validated['date'] ?? null,
            'fecha_inicio' => $validated['start_date'] ?? null,
            'fecha_fin' => $validated['end_date'] ?? null,
            'es_recurrente' => (bool) $validated['is_recurring'],
            'dia_semana' => $validated['weekday'] ?? null,
            'hora_inicio' => $validated['start_time'] !== null ? $this->normalizeTimeForStorage($validated['start_time']) : null,
            'hora_fin' => $validated['end_time'] !== null ? $this->normalizeTimeForStorage($validated['end_time']) : null,
            'motivo' => $validated['reason'] ?? null,
            'activo' => (bool) $validated['is_active'],
        ];
    }

    public function messages(): array
    {
        return [
            'resource_id.integer' => 'The selected resource_id is invalid.',
            'resource_id.exists' => 'The selected resource does not belong to this business.',
            'block_type_id.required' => 'The block_type_id field is required.',
            'block_type_id.integer' => 'The selected block_type_id is invalid.',
            'block_type_id.exists' => 'The selected block_type_id does not exist.',
            'date.date' => 'The date field must be a valid date.',
            'start_date.date' => 'The start_date must be a valid date.',
            'end_date.date' => 'The end_date must be a valid date.',
            'end_date.after_or_equal' => 'The end_date must be the same as or after start_date.',
            'is_recurring.required' => 'The is_recurring field is required.',
            'is_recurring.boolean' => 'The is_recurring field must be true or false.',
            'weekday.integer' => 'The weekday must be an integer between 0 and 6.',
            'weekday.in' => 'The weekday must be a value between 0 and 6.',
            'start_time.date_format' => 'The start_time must match the format H:i.',
            'end_time.date_format' => 'The end_time must match the format H:i.',
            'reason.string' => 'The reason field must be a valid string.',
            'reason.max' => 'The reason field may not be greater than 255 characters.',
            'is_active.required' => 'The is_active field is required.',
            'is_active.boolean' => 'The is_active field must be true or false.',
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

<?php

namespace App\Http\Requests\Api\V1\Resources;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $this->input('name', '')));
        $capacity = trim((string) $this->input('capacity', ''));

        $minCapacity = trim((string) $this->input('minCapacity', ''));
        $publicNotes = trim((string) $this->input('publicNotes', ''));

        $this->merge([
            'name' => $name !== '' ? $name : null,
            'capacity' => $capacity !== '' ? $capacity : null,
            'is_active' => $this->has('is_active')
                ? $this->normalizeBoolean($this->input('is_active'))
                : null,
            'isCombinable' => $this->normalizeBoolean($this->input('isCombinable', false)),
            'minCapacity' => $minCapacity !== '' ? $minCapacity : null,
            'publicNotes' => $publicNotes !== '' ? $publicNotes : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = (string) $value;

                    if (! preg_match('/\p{L}/u', $value)) {
                        $fail('The name must contain at least one letter.');
                        return;
                    }

                    if (preg_match('/^\d+$/', $value)) {
                        $fail('The name cannot contain only numbers.');
                    }
                },
            ],
            'resource_type_id' => ['required', 'integer', 'exists:tipos_recurso,id'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'minCapacity' => [
                'nullable',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $capacity = $this->input('capacity');
                    if ($value !== null && $capacity !== null && (int) $value > (int) $capacity) {
                        $fail('The minimum capacity cannot exceed the maximum capacity.');
                    }
                },
            ],
            'isCombinable' => ['required', 'boolean'],
            'publicNotes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function resourceAttributes(): array
    {
        $validated = $this->validated();

        return [
            'nombre' => $validated['name'],
            'tipo_recurso_id' => (int) $validated['resource_type_id'],
            'capacidad' => array_key_exists('capacity', $validated) ? ($validated['capacity'] !== null ? (int) $validated['capacity'] : null) : null,
            'activo' => (bool) $validated['is_active'],
            'capacidad_minima' => $validated['minCapacity'] ?? null,
            'combinable' => $validated['isCombinable'],
            'notas_publicas' => $validated['publicNotes'] ?? null,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'resource_type_id.required' => 'The resource_type_id field is required.',
            'resource_type_id.integer' => 'The selected resource_type_id is invalid.',
            'resource_type_id.exists' => 'The selected resource_type_id does not exist.',
            'capacity.integer' => 'The capacity field must be an integer.',
            'capacity.min' => 'The capacity field must be at least 1.',
            'is_active.required' => 'The is_active field is required.',
            'is_active.boolean' => 'The is_active field must be true or false.',
            'minCapacity.integer' => 'The minimum capacity must be an integer.',
            'minCapacity.min' => 'The minimum capacity must be at least 1.',
            'isCombinable.required' => 'The is_combinable field is required.',
            'isCombinable.boolean' => 'The is_combinable field must be true or false.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

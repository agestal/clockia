<?php

namespace App\Http\Requests\Api\V1\Services;

use App\Models\Negocio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $this->input('name', '')));
        $description = trim((string) $this->input('description', ''));
        $durationMinutes = trim((string) $this->input('duration_minutes', ''));
        $basePrice = trim((string) $this->input('base_price', ''));
        $resourceIds = $this->input('resource_ids', []);

        $publicNotes = trim((string) $this->input('publicNotes', ''));
        $priorInstructions = trim((string) $this->input('priorInstructions', ''));
        $requiredDocumentation = trim((string) $this->input('requiredDocumentation', ''));
        $minCancellationHours = trim((string) $this->input('minCancellationHours', ''));
        $depositPercentage = trim((string) $this->input('depositPercentage', ''));

        $this->merge([
            'name' => $name !== '' ? $name : null,
            'description' => $description !== '' ? $description : null,
            'duration_minutes' => $durationMinutes !== '' ? $durationMinutes : null,
            'base_price' => $basePrice !== '' ? $this->normalizeDecimal($basePrice) : null,
            'requires_payment' => $this->has('requires_payment')
                ? $this->normalizeBoolean($this->input('requires_payment'))
                : null,
            'is_active' => $this->has('is_active')
                ? $this->normalizeBoolean($this->input('is_active'))
                : null,
            'resource_ids' => is_array($resourceIds)
                ? array_values(array_unique(array_filter($resourceIds, fn ($value) => $value !== null && $value !== '')))
                : [],
            'isRefundable' => $this->normalizeBoolean($this->input('isRefundable', true)),
            'pricePerTimeUnit' => $this->normalizeBoolean($this->input('pricePerTimeUnit', false)),
            'publicNotes' => $publicNotes !== '' ? $publicNotes : null,
            'priorInstructions' => $priorInstructions !== '' ? $priorInstructions : null,
            'requiredDocumentation' => $requiredDocumentation !== '' ? $requiredDocumentation : null,
            'minCancellationHours' => $minCancellationHours !== '' ? $minCancellationHours : null,
            'depositPercentage' => $depositPercentage !== '' ? $this->normalizeDecimal($depositPercentage) : null,
        ]);
    }

    public function rules(): array
    {
        $business = $this->route('business');
        $businessId = $business instanceof Negocio ? $business->getKey() : null;

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
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'price_type_id' => ['required', 'integer', 'exists:tipos_precio,id'],
            'requires_payment' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'publicNotes' => ['nullable', 'string'],
            'priorInstructions' => ['nullable', 'string'],
            'requiredDocumentation' => ['nullable', 'string'],
            'minCancellationHours' => ['nullable', 'integer', 'min:0'],
            'isRefundable' => ['required', 'boolean'],
            'depositPercentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pricePerTimeUnit' => ['required', 'boolean'],
            'resource_ids' => ['nullable', 'array'],
            'resource_ids.*' => [
                'integer',
                Rule::exists('recursos', 'id')->where(
                    fn ($query) => $query->where('negocio_id', $businessId)
                ),
            ],
        ];
    }

    public function serviceAttributes(): array
    {
        $validated = $this->validated();

        return [
            'nombre' => $validated['name'],
            'descripcion' => $validated['description'] ?? null,
            'duracion_minutos' => (int) $validated['duration_minutes'],
            'precio_base' => number_format((float) $validated['base_price'], 2, '.', ''),
            'tipo_precio_id' => (int) $validated['price_type_id'],
            'requiere_pago' => (bool) $validated['requires_payment'],
            'activo' => (bool) $validated['is_active'],
            'notas_publicas' => $validated['publicNotes'] ?? null,
            'instrucciones_previas' => $validated['priorInstructions'] ?? null,
            'documentacion_requerida' => $validated['requiredDocumentation'] ?? null,
            'horas_minimas_cancelacion' => $validated['minCancellationHours'] ?? null,
            'es_reembolsable' => $validated['isRefundable'],
            'porcentaje_senal' => isset($validated['depositPercentage']) && $validated['depositPercentage'] !== null
                ? number_format((float) $validated['depositPercentage'], 2, '.', '')
                : null,
            'precio_por_unidad_tiempo' => $validated['pricePerTimeUnit'],
        ];
    }

    public function resourceIds(): array
    {
        return collect($this->validated('resource_ids', []))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'description.string' => 'The description must be a valid string.',
            'duration_minutes.required' => 'The duration_minutes field is required.',
            'duration_minutes.integer' => 'The duration_minutes field must be an integer.',
            'duration_minutes.min' => 'The duration_minutes field must be at least 1.',
            'base_price.required' => 'The base_price field is required.',
            'base_price.numeric' => 'The base_price field must be a valid number.',
            'base_price.min' => 'The base_price field must be at least 0.',
            'base_price.max' => 'The base_price field may not be greater than 99999999.99.',
            'price_type_id.required' => 'The price_type_id field is required.',
            'price_type_id.integer' => 'The selected price_type_id is invalid.',
            'price_type_id.exists' => 'The selected price_type_id does not exist.',
            'requires_payment.required' => 'The requires_payment field is required.',
            'requires_payment.boolean' => 'The requires_payment field must be true or false.',
            'is_active.required' => 'The is_active field is required.',
            'is_active.boolean' => 'The is_active field must be true or false.',
            'resource_ids.array' => 'The resource_ids field must be an array.',
            'resource_ids.*.integer' => 'Each resource id must be a valid integer.',
            'resource_ids.*.exists' => 'One of the selected resources does not belong to this business.',
            'minCancellationHours.integer' => 'The minimum cancellation hours must be an integer.',
            'minCancellationHours.min' => 'The minimum cancellation hours cannot be negative.',
            'isRefundable.required' => 'The is_refundable field is required.',
            'isRefundable.boolean' => 'The is_refundable field must be true or false.',
            'depositPercentage.numeric' => 'The deposit percentage must be a number.',
            'depositPercentage.min' => 'The deposit percentage cannot be negative.',
            'depositPercentage.max' => 'The deposit percentage cannot exceed 100.',
            'pricePerTimeUnit.required' => 'The price_per_time_unit field is required.',
            'pricePerTimeUnit.boolean' => 'The price_per_time_unit field must be true or false.',
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

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', '')));
        $email = preg_replace('/\s+/u', ' ', trim((string) $this->input('email', '')));
        $telefono = preg_replace('/\s+/u', ' ', trim((string) $this->input('telefono', '')));
        $descripcionPublica = trim((string) $this->input('descripcion_publica', ''));
        $direccion = preg_replace('/\s+/u', ' ', trim((string) $this->input('direccion', '')));
        $urlPublica = trim((string) $this->input('url_publica', ''));
        $politicaCancelacion = trim((string) $this->input('politica_cancelacion', ''));
        $horasMinCancelacion = trim((string) $this->input('horas_minimas_cancelacion', ''));
        $maxRecursosCombinables = trim((string) $this->input('max_recursos_combinables', ''));
        $chatPersonality = trim((string) $this->input('chat_personality', ''));
        $chatRequiredFields = trim((string) $this->input('chat_required_fields', ''));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'email' => $email !== '' ? $email : null,
            'telefono' => $telefono !== '' ? $telefono : null,
            'activo' => $this->normalizeBoolean($this->input('activo')),
            'descripcion_publica' => $descripcionPublica !== '' ? $descripcionPublica : null,
            'direccion' => $direccion !== '' ? $direccion : null,
            'url_publica' => $urlPublica !== '' ? $urlPublica : null,
            'politica_cancelacion' => $politicaCancelacion !== '' ? $politicaCancelacion : null,
            'horas_minimas_cancelacion' => $horasMinCancelacion !== '' ? $horasMinCancelacion : null,
            'permite_modificacion' => $this->normalizeBoolean($this->input('permite_modificacion')),
            'max_recursos_combinables' => $maxRecursosCombinables !== '' ? $maxRecursosCombinables : null,
            'chat_personality' => $chatPersonality !== '' ? $chatPersonality : null,
            'chat_required_fields' => $chatRequiredFields !== '' ? $chatRequiredFields : null,
            'chat_system_rules' => trim((string) $this->input('chat_system_rules', '')) !== '' ? trim((string) $this->input('chat_system_rules', '')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = (string) $value;

                    if (! preg_match('/\p{L}/u', $value)) {
                        $fail('El nombre debe contener al menos una letra.');
                        return;
                    }

                    if (preg_match('/^\d+$/', $value)) {
                        $fail('El nombre no puede estar formado solo por números.');
                    }
                },
            ],
            'tipo_negocio_id' => ['required', 'integer', 'exists:tipos_negocio,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'zona_horaria' => ['required', 'string', 'timezone'],
            'activo' => ['required', 'boolean'],
            'descripcion_publica' => ['nullable', 'string'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'url_publica' => ['nullable', 'string', 'url', 'max:500'],
            'politica_cancelacion' => ['nullable', 'string'],
            'horas_minimas_cancelacion' => ['nullable', 'integer', 'min:0'],
            'permite_modificacion' => ['required', 'boolean'],
            'max_recursos_combinables' => ['nullable', 'integer', 'min:1', 'max:5'],
            'chat_personality' => ['nullable', 'string'],
            'chat_required_fields' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || trim($value) === '') {
                        return;
                    }
                    $decoded = json_decode($value, true);
                    if (! is_array($decoded)) {
                        $fail('La configuración de campos requeridos debe ser un JSON válido.');
                    }
                },
            ],
            'chat_system_rules' => ['nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if (isset($validated['chat_required_fields']) && is_string($validated['chat_required_fields'])) {
            $decoded = json_decode($validated['chat_required_fields'], true);
            $validated['chat_required_fields'] = is_array($decoded) ? $decoded : null;
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'tipo_negocio_id.required' => 'Debes seleccionar un tipo de negocio.',
            'tipo_negocio_id.integer' => 'El tipo de negocio seleccionado no es válido.',
            'tipo_negocio_id.exists' => 'El tipo de negocio seleccionado no existe.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede superar los 255 caracteres.',
            'telefono.string' => 'El teléfono debe ser un texto válido.',
            'telefono.max' => 'El teléfono no puede superar los 255 caracteres.',
            'zona_horaria.required' => 'La zona horaria es obligatoria.',
            'zona_horaria.string' => 'La zona horaria debe ser un texto válido.',
            'zona_horaria.timezone' => 'La zona horaria seleccionada no es válida.',
            'activo.required' => 'Debes indicar si el negocio está activo.',
            'activo.boolean' => 'El estado del negocio no es válido.',
            'direccion.max' => 'La dirección no puede superar los 500 caracteres.',
            'url_publica.url' => 'La URL pública debe ser una dirección web válida.',
            'url_publica.max' => 'La URL pública no puede superar los 500 caracteres.',
            'horas_minimas_cancelacion.integer' => 'Las horas mínimas de cancelación deben ser un número entero.',
            'horas_minimas_cancelacion.min' => 'Las horas mínimas de cancelación no pueden ser negativas.',
            'permite_modificacion.required' => 'Debes indicar si se permiten modificaciones.',
            'permite_modificacion.boolean' => 'El valor de permite modificación no es válido.',
            'max_recursos_combinables.integer' => 'El máximo de recursos combinables debe ser un número entero.',
            'max_recursos_combinables.min' => 'El máximo de recursos combinables debe ser al menos 1.',
            'max_recursos_combinables.max' => 'El máximo de recursos combinables no puede superar 5.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

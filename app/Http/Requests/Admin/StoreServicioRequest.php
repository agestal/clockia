<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', '')));
        $descripcion = trim((string) $this->input('descripcion', ''));
        $duracionMinutos = trim((string) $this->input('duracion_minutos', ''));
        $precioBase = trim((string) $this->input('precio_base', ''));
        $recursos = $this->input('recursos', []);
        $notasPublicas = trim((string) $this->input('notas_publicas', ''));
        $instruccionesPrevias = trim((string) $this->input('instrucciones_previas', ''));
        $documentacionRequerida = trim((string) $this->input('documentacion_requerida', ''));
        $horasMinCancelacion = trim((string) $this->input('horas_minimas_cancelacion', ''));
        $porcentajeSenal = trim((string) $this->input('porcentaje_senal', ''));
        $aforo = trim((string) $this->input('aforo', ''));
        $horaInicio = $this->normalizeTimeForValidation($this->input('hora_inicio'));
        $horaFin = $this->normalizeTimeForValidation($this->input('hora_fin'));

        $this->merge([
            'nombre' => $nombre !== '' ? $nombre : null,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'duracion_minutos' => $duracionMinutos !== '' ? $duracionMinutos : null,
            'aforo' => $aforo !== '' ? $aforo : null,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'precio_base' => $precioBase !== '' ? $this->normalizeDecimal($precioBase) : null,
            'requiere_pago' => $this->normalizeBoolean($this->input('requiere_pago')),
            'activo' => $this->normalizeBoolean($this->input('activo')),
            'recursos' => is_array($recursos) ? array_values(array_unique(array_filter($recursos, fn ($value) => $value !== null && $value !== ''))) : [],
            'notas_publicas' => $notasPublicas !== '' ? $notasPublicas : null,
            'instrucciones_previas' => $instruccionesPrevias !== '' ? $instruccionesPrevias : null,
            'documentacion_requerida' => $documentacionRequerida !== '' ? $documentacionRequerida : null,
            'horas_minimas_cancelacion' => $horasMinCancelacion !== '' ? $horasMinCancelacion : null,
            'es_reembolsable' => $this->normalizeBoolean($this->input('es_reembolsable')),
            'porcentaje_senal' => $porcentajeSenal !== '' ? $this->normalizeDecimal($porcentajeSenal) : null,
            'precio_por_unidad_tiempo' => $this->normalizeBoolean($this->input('precio_por_unidad_tiempo')),
        ]);
    }

    public function rules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
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
            'descripcion' => ['nullable', 'string'],
            'duracion_minutos' => ['required', 'integer', 'min:1'],
            'aforo' => ['nullable', 'integer', 'min:1'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'precio_base' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tipo_precio_id' => ['required', 'integer', 'exists:tipos_precio,id'],
            'requiere_pago' => ['required', 'boolean'],
            'activo' => ['required', 'boolean'],
            'recursos' => ['nullable', 'array'],
            'recursos.*' => ['integer', 'exists:recursos,id'],
            'notas_publicas' => ['nullable', 'string'],
            'instrucciones_previas' => ['nullable', 'string'],
            'documentacion_requerida' => ['nullable', 'string'],
            'horas_minimas_cancelacion' => ['nullable', 'integer', 'min:0'],
            'es_reembolsable' => ['required', 'boolean'],
            'porcentaje_senal' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'precio_por_unidad_tiempo' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $aforo = $this->input('aforo');
            $horaInicio = $this->input('hora_inicio');
            $horaFin = $this->input('hora_fin');

            $hasDynamicField = $aforo !== null || $horaInicio !== null || $horaFin !== null;

            if (! $hasDynamicField) {
                return;
            }

            if ($aforo === null || $horaInicio === null || $horaFin === null) {
                $validator->errors()->add('aforo', 'Para activar la programación dinámica debes informar aforo, hora de inicio y hora de fin.');

                return;
            }

            if ($horaFin <= $horaInicio) {
                $validator->errors()->add('hora_fin', 'La hora de fin debe ser posterior a la hora de inicio.');
            }
        });
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if (array_key_exists('precio_base', $validated) && $validated['precio_base'] !== null) {
            $validated['precio_base'] = number_format((float) $validated['precio_base'], 2, '.', '');
        }

        if (array_key_exists('porcentaje_senal', $validated) && $validated['porcentaje_senal'] !== null) {
            $validated['porcentaje_senal'] = number_format((float) $validated['porcentaje_senal'], 2, '.', '');
        }

        if (array_key_exists('hora_inicio', $validated) && $validated['hora_inicio'] !== null) {
            $validated['hora_inicio'] = $this->normalizeTimeForStorage($validated['hora_inicio']);
        }

        if (array_key_exists('hora_fin', $validated) && $validated['hora_fin'] !== null) {
            $validated['hora_fin'] = $this->normalizeTimeForStorage($validated['hora_fin']);
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'negocio_id.required' => 'Debes seleccionar un negocio.',
            'negocio_id.integer' => 'El negocio seleccionado no es válido.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'duracion_minutos.required' => 'La duración es obligatoria.',
            'duracion_minutos.integer' => 'La duración debe ser un número entero válido.',
            'duracion_minutos.min' => 'La duración debe ser al menos 1 minuto.',
            'aforo.integer' => 'El aforo debe ser un número entero válido.',
            'aforo.min' => 'El aforo debe ser al menos 1 plaza.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'precio_base.required' => 'El precio base es obligatorio.',
            'precio_base.numeric' => 'El precio base debe ser un número válido.',
            'precio_base.min' => 'El precio base no puede ser negativo.',
            'precio_base.max' => 'El precio base no puede superar 99999999.99.',
            'tipo_precio_id.required' => 'Debes seleccionar un tipo de precio.',
            'tipo_precio_id.integer' => 'El tipo de precio seleccionado no es válido.',
            'tipo_precio_id.exists' => 'El tipo de precio seleccionado no existe.',
            'requiere_pago.required' => 'Debes indicar si el servicio requiere pago.',
            'requiere_pago.boolean' => 'El valor de requiere pago no es válido.',
            'activo.required' => 'Debes indicar si el servicio está activo.',
            'activo.boolean' => 'El estado del servicio no es válido.',
            'recursos.array' => 'Los recursos seleccionados no son válidos.',
            'recursos.*.integer' => 'Cada recurso seleccionado debe ser válido.',
            'recursos.*.exists' => 'Uno de los recursos seleccionados no existe.',
            'horas_minimas_cancelacion.integer' => 'Las horas mínimas de cancelación deben ser un número entero.',
            'horas_minimas_cancelacion.min' => 'Las horas mínimas de cancelación no pueden ser negativas.',
            'es_reembolsable.required' => 'Debes indicar si el servicio es reembolsable.',
            'es_reembolsable.boolean' => 'El valor de es reembolsable no es válido.',
            'porcentaje_senal.numeric' => 'El porcentaje de señal debe ser un número válido.',
            'porcentaje_senal.min' => 'El porcentaje de señal no puede ser negativo.',
            'porcentaje_senal.max' => 'El porcentaje de señal no puede superar el 100%.',
            'precio_por_unidad_tiempo.required' => 'Debes indicar si el precio es por unidad de tiempo.',
            'precio_por_unidad_tiempo.boolean' => 'El valor de precio por unidad de tiempo no es válido.',
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

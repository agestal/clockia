<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $referenciaExterna = trim((string) $this->input('referencia_externa', ''));
        $importe = trim((string) $this->input('importe', ''));
        $fechaPago = trim((string) $this->input('fecha_pago', ''));
        $enlacePagoExterno = trim((string) $this->input('enlace_pago_externo', ''));

        $this->merge([
            'referencia_externa' => $referenciaExterna !== '' ? $referenciaExterna : null,
            'importe' => $importe !== '' ? $this->normalizeDecimal($importe) : null,
            'fecha_pago' => $fechaPago !== '' ? $fechaPago : null,
            'enlace_pago_externo' => $enlacePagoExterno !== '' ? $enlacePagoExterno : null,
            'iniciado_por_bot' => $this->normalizeBoolean($this->input('iniciado_por_bot')),
            'concepto_pago_id' => $this->filled('concepto_pago_id') ? (int) $this->input('concepto_pago_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'tipo_pago_id' => ['required', 'integer', 'exists:tipos_pago,id'],
            'estado_pago_id' => ['required', 'integer', 'exists:estados_pago,id'],
            'concepto_pago_id' => ['nullable', 'integer', 'exists:conceptos_pago,id'],
            'importe' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'referencia_externa' => ['nullable', 'string', 'max:255'],
            'fecha_pago' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'enlace_pago_externo' => ['nullable', 'string', 'url', 'max:1000'],
            'iniciado_por_bot' => ['required', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if (array_key_exists('importe', $validated) && $validated['importe'] !== null) {
            $validated['importe'] = number_format((float) $validated['importe'], 2, '.', '');
        }

        if (array_key_exists('fecha_pago', $validated)) {
            $validated['fecha_pago'] = $validated['fecha_pago']
                ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['fecha_pago'])->format('Y-m-d H:i:s')
                : null;
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'reserva_id.required' => 'Debes seleccionar una reserva.',
            'reserva_id.integer' => 'La reserva seleccionada no es válida.',
            'reserva_id.exists' => 'La reserva seleccionada no existe.',
            'tipo_pago_id.required' => 'Debes seleccionar un tipo de pago.',
            'tipo_pago_id.integer' => 'El tipo de pago seleccionado no es válido.',
            'tipo_pago_id.exists' => 'El tipo de pago seleccionado no existe.',
            'estado_pago_id.required' => 'Debes seleccionar un estado de pago.',
            'estado_pago_id.integer' => 'El estado de pago seleccionado no es válido.',
            'estado_pago_id.exists' => 'El estado de pago seleccionado no existe.',
            'concepto_pago_id.integer' => 'El concepto de pago seleccionado no es válido.',
            'concepto_pago_id.exists' => 'El concepto de pago seleccionado no existe.',
            'importe.required' => 'El importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser un número válido.',
            'importe.min' => 'El importe no puede ser negativo.',
            'importe.max' => 'El importe no puede superar 99999999.99.',
            'referencia_externa.string' => 'La referencia externa debe ser un texto válido.',
            'referencia_externa.max' => 'La referencia externa no puede superar los 255 caracteres.',
            'fecha_pago.date_format' => 'La fecha de pago debe tener un formato válido.',
            'enlace_pago_externo.url' => 'El enlace de pago externo debe ser una URL válida.',
            'enlace_pago_externo.max' => 'El enlace de pago externo no puede superar los 1000 caracteres.',
            'iniciado_por_bot.required' => 'Debes indicar si el pago fue iniciado por el bot.',
            'iniciado_por_bot.boolean' => 'El valor de iniciado por bot no es válido.',
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

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notif_email_destino' => trim((string) $this->input('notif_email_destino', '')) ?: null,
            'notif_reserva_nueva' => $this->normalizeBoolean($this->input('notif_reserva_nueva')),
            'notif_reserva_modificada' => $this->normalizeBoolean($this->input('notif_reserva_modificada')),
            'notif_anulacion_reserva' => $this->normalizeBoolean($this->input('notif_anulacion_reserva')),
            'notif_encuesta_respondida' => $this->normalizeBoolean($this->input('notif_encuesta_respondida')),
            'notif_aforo_lleno_experiencia' => $this->normalizeBoolean($this->input('notif_aforo_lleno_experiencia')),
            'notif_aforo_lleno_dia' => $this->normalizeBoolean($this->input('notif_aforo_lleno_dia')),
        ]);
    }

    public function rules(): array
    {
        return [
            'notif_email_destino' => ['nullable', 'email', 'max:255'],
            'notif_reserva_nueva' => ['required', 'boolean'],
            'notif_reserva_modificada' => ['required', 'boolean'],
            'notif_anulacion_reserva' => ['required', 'boolean'],
            'notif_encuesta_respondida' => ['required', 'boolean'],
            'notif_aforo_lleno_experiencia' => ['required', 'boolean'],
            'notif_aforo_lleno_dia' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'notif_email_destino.email' => 'El email de destino debe ser valido.',
            'notif_email_destino.max' => 'El email de destino no puede superar los 255 caracteres.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}

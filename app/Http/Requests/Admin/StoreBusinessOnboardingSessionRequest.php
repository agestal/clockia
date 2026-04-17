<?php

namespace App\Http\Requests\Admin;

use App\Support\OnboardingUrl;
use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessOnboardingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $sourceUrl = trim((string) $this->input('source_url', ''));
        $requestedBusinessName = preg_replace('/\s+/u', ' ', trim((string) $this->input('requested_business_name', '')));
        $requestedAdminName = preg_replace('/\s+/u', ' ', trim((string) $this->input('requested_admin_name', '')));
        $requestedAdminEmail = trim((string) $this->input('requested_admin_email', ''));

        if ($sourceUrl !== '') {
            try {
                $sourceUrl = OnboardingUrl::normalize($sourceUrl);
            } catch (\Throwable) {
                // The url rule will surface the validation error afterwards.
            }
        }

        $this->merge([
            'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
            'requested_business_name' => $requestedBusinessName !== '' ? $requestedBusinessName : null,
            'requested_admin_name' => $requestedAdminName !== '' ? $requestedAdminName : null,
            'requested_admin_email' => $requestedAdminEmail !== '' ? strtolower($requestedAdminEmail) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'source_url' => [
                'required',
                'string',
                'url',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! OnboardingUrl::isAllowed($value)) {
                        $fail('La URL no es valida para el configurador.');
                    }
                },
            ],
            'requested_tipo_negocio_id' => ['required', 'integer', 'exists:tipos_negocio,id'],
            'requested_business_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'requested_admin_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'requested_admin_email' => ['nullable', 'email', 'max:255'],
            'requested_admin_password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_url.required' => 'La URL del negocio es obligatoria.',
            'source_url.url' => 'La URL del negocio debe ser valida.',
            'source_url.max' => 'La URL del negocio no puede superar los 500 caracteres.',
            'requested_tipo_negocio_id.required' => 'Debes seleccionar un tipo de negocio.',
            'requested_tipo_negocio_id.integer' => 'El tipo de negocio seleccionado no es valido.',
            'requested_tipo_negocio_id.exists' => 'El tipo de negocio seleccionado no existe.',
            'requested_business_name.min' => 'El nombre del negocio debe tener al menos 2 caracteres.',
            'requested_business_name.max' => 'El nombre del negocio no puede superar los 255 caracteres.',
            'requested_admin_name.min' => 'El nombre del administrador debe tener al menos 2 caracteres.',
            'requested_admin_name.max' => 'El nombre del administrador no puede superar los 255 caracteres.',
            'requested_admin_email.email' => 'El email del administrador debe ser valido.',
            'requested_admin_email.max' => 'El email del administrador no puede superar los 255 caracteres.',
            'requested_admin_password.min' => 'La contraseña del administrador debe tener al menos 8 caracteres.',
            'requested_admin_password.confirmed' => 'La confirmacion de la contraseña no coincide.',
        ];
    }
}

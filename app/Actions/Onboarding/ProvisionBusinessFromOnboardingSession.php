<?php

namespace App\Actions\Onboarding;

use App\Models\BusinessOnboardingSession;
use App\Models\EncuestaPlantilla;
use App\Models\Negocio;
use App\Models\PlantillaEmail;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProvisionBusinessFromOnboardingSession
{
    public function handle(BusinessOnboardingSession $session): Negocio
    {
        if ($session->provisionedNegocio) {
            return $session->provisionedNegocio;
        }

        $draft = $session->draft();
        $business = Arr::get($draft, 'business', []);
        $admin = Arr::get($draft, 'admin', []);
        $errors = [];

        if (! filled($business['nombre'] ?? null)) {
            $errors['business.nombre'][] = 'El nombre del negocio sigue siendo obligatorio.';
        }

        if (! filled($business['tipo_negocio_id'] ?? null)) {
            $errors['business.tipo_negocio_id'][] = 'El tipo de negocio sigue siendo obligatorio.';
        }

        if (! filled($business['zona_horaria'] ?? null)) {
            $errors['business.zona_horaria'][] = 'La zona horaria sigue siendo obligatoria.';
        }

        if (! filled($admin['email'] ?? null)) {
            $errors['admin.email'][] = 'El email del administrador es obligatorio para crear el acceso.';
        }

        if ($session->requested_admin_password_hash === null) {
            $errors['admin.password'][] = 'La contraseña del administrador sigue siendo obligatoria.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($session, $draft, $business, $admin) {
            $negocio = Negocio::create([
                'nombre' => (string) $business['nombre'],
                'tipo_negocio_id' => (int) $business['tipo_negocio_id'],
                'email' => $business['email'] ?? null,
                'telefono' => $business['telefono'] ?? null,
                'zona_horaria' => (string) $business['zona_horaria'],
                'dias_apertura' => is_array($business['dias_apertura'] ?? null) ? $business['dias_apertura'] : null,
                'activo' => (bool) ($business['activo'] ?? true),
                'descripcion_publica' => $business['descripcion_publica'] ?? null,
                'direccion' => $business['direccion'] ?? null,
                'url_publica' => $business['url_publica'] ?? $session->source_url,
                'permite_modificacion' => (bool) ($business['permite_modificacion'] ?? true),
                'mail_confirmacion_activo' => true,
                'mail_recordatorio_activo' => true,
                'mail_recordatorio_horas_antes' => 24,
                'mail_encuesta_activo' => true,
                'mail_encuesta_horas_despues' => 24,
                'notif_email_destino' => (string) $admin['email'],
                'notif_reserva_nueva' => true,
                'notif_reserva_modificada' => true,
                'notif_anulacion_reserva' => true,
                'notif_encuesta_respondida' => true,
                'notif_aforo_lleno_experiencia' => false,
                'notif_aforo_lleno_dia' => false,
                'widget_enabled' => true,
                'chat_widget_enabled' => true,
            ]);

            PlantillaEmail::ensureDefaultsForBusiness($negocio);
            EncuestaPlantilla::ensureDefaultForBusiness($negocio);

            $adminName = trim((string) ($admin['name'] ?? '')) ?: 'Admin '.$negocio->nombre;
            $user = User::query()->where('email', (string) $admin['email'])->first();

            if (! $user) {
                $user = User::create([
                    'name' => $adminName,
                    'email' => (string) $admin['email'],
                    'role' => User::ROLE_BUSINESS_ADMIN,
                    'password' => (string) $session->requested_admin_password_hash,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->fill([
                    'name' => $adminName,
                    'password' => (string) $session->requested_admin_password_hash,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);

                if (! $user->hasFullAdminAccess()) {
                    $user->role = User::ROLE_BUSINESS_ADMIN;
                }

                $user->save();
            }

            $user->negocios()->syncWithoutDetaching([$negocio->id]);

            $session->update([
                'status' => BusinessOnboardingSession::STATUS_PROVISIONED,
                'confirmed_at' => $session->confirmed_at ?? now(),
                'provisioned_at' => now(),
                'provisioned_negocio_id' => $negocio->id,
                'draft_payload' => $draft,
                'last_error' => null,
            ]);

            return $negocio;
        });
    }
}

<?php

namespace App\Support;

use App\Models\Bloqueo;
use App\Models\Cliente;
use App\Models\Disponibilidad;
use App\Models\EncuestaPlantilla;
use App\Models\Negocio;
use App\Models\Pago;
use App\Models\PlantillaEmail;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminAccess
{
    /**
     * @return list<int>|null
     */
    public function accessibleBusinessIds(?User $user): ?array
    {
        if (! $user || $user->hasFullAdminAccess()) {
            return null;
        }

        return $user->negocios()
            ->orderBy('negocios.id')
            ->pluck('negocios.id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    public function accessibleBusinessesQuery(?User $user): Builder
    {
        $query = Negocio::query();

        return $this->scopeBusinesses($query, $user, 'id');
    }

    public function scopeBusinesses(Builder $query, ?User $user, string $column = 'negocio_id'): Builder
    {
        $businessIds = $this->accessibleBusinessIds($user);

        if ($businessIds === null) {
            return $query;
        }

        return $query->whereIn($column, $businessIds !== [] ? $businessIds : [0]);
    }

    public function scopeBusinessRelation(
        Builder $query,
        ?User $user,
        string $relation,
        string $column = 'negocio_id'
    ): Builder {
        $businessIds = $this->accessibleBusinessIds($user);

        if ($businessIds === null) {
            return $query;
        }

        return $query->whereHas($relation, function (Builder $relationQuery) use ($businessIds, $column) {
            $relationQuery->whereIn($column, $businessIds !== [] ? $businessIds : [0]);
        });
    }

    public function scopeClients(Builder $query, ?User $user): Builder
    {
        $businessIds = $this->accessibleBusinessIds($user);

        if ($businessIds === null) {
            return $query;
        }

        return $query->where(function (Builder $clientQuery) use ($businessIds) {
            $clientQuery
                ->doesntHave('reservas')
                ->orWhere(function (Builder $subsetQuery) use ($businessIds) {
                    $subsetQuery
                        ->whereHas('reservas', function (Builder $reservationQuery) use ($businessIds) {
                            $reservationQuery->whereIn('negocio_id', $businessIds !== [] ? $businessIds : [0]);
                        })
                        ->whereDoesntHave('reservas', function (Builder $reservationQuery) use ($businessIds) {
                            $reservationQuery->whereNotIn('negocio_id', $businessIds !== [] ? $businessIds : [0]);
                        });
                });
        });
    }

    public function canAccessBusinessId(?User $user, ?int $businessId): bool
    {
        if ($businessId === null) {
            return false;
        }

        if (! $user) {
            return false;
        }

        if ($user->hasFullAdminAccess()) {
            return true;
        }

        return in_array($businessId, $this->accessibleBusinessIds($user) ?? [], true);
    }

    public function canAccessModel(?User $user, mixed $model): bool
    {
        if (! $user || ! $model) {
            return false;
        }

        if ($user->hasFullAdminAccess()) {
            return true;
        }

        return match (true) {
            $model instanceof Negocio => $this->canAccessBusinessId($user, $model->id),
            $model instanceof Servicio,
            $model instanceof Recurso,
            $model instanceof Reserva,
            $model instanceof PlantillaEmail,
            $model instanceof EncuestaPlantilla => $this->canAccessBusinessId($user, $model->negocio_id),
            $model instanceof Disponibilidad => $this->canAccessBusinessId($user, $model->recurso?->negocio_id),
            $model instanceof Bloqueo => $this->canAccessBusinessId($user, $model->negocio_id ?? $model->recurso?->negocio_id),
            $model instanceof Pago => $this->canAccessBusinessId($user, $model->reserva?->negocio_id),
            $model instanceof Cliente => $this->canAccessClient($user, $model),
            default => true,
        };
    }

    public function allowsAdminRoute(?User $user, ?string $routeName): bool
    {
        if (! $user || $routeName === null || $routeName === '') {
            return false;
        }

        if ($user->hasFullAdminAccess()) {
            return true;
        }

        $allowedPatterns = [
            'admin.negocios.*',
            'admin.servicios.*',
            'admin.recursos.*',
            'admin.disponibilidades.*',
            'admin.bloqueos.*',
            'admin.reservas.*',
            'admin.clientes.*',
            'admin.pagos.*',
            'admin.calendario.*',
            'admin.plantillas-email.*',
            'admin.encuesta-plantillas.*',
            'admin.ajax.negocios.search',
            'admin.ajax.servicios.search',
            'admin.ajax.recursos.search',
            'admin.ajax.clientes.search',
            'admin.ajax.reservas.search',
            'admin.ajax.tipos-negocio.search',
            'admin.ajax.tipos-precio.search',
            'admin.ajax.tipos-recurso.search',
            'admin.ajax.tipos-bloqueo.search',
            'admin.ajax.estados-reserva.search',
            'admin.ajax.tipos-pago.search',
            'admin.ajax.estados-pago.search',
            'admin.ajax.conceptos-pago.search',
        ];

        foreach ($allowedPatterns as $pattern) {
            if (str_ends_with($pattern, '*')) {
                $prefix = substr($pattern, 0, -1);

                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }

                continue;
            }

            if ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }

    private function canAccessClient(User $user, Cliente $cliente): bool
    {
        $businessIds = $this->accessibleBusinessIds($user);

        if ($businessIds === null) {
            return true;
        }

        $hasAllowedReservations = $cliente->reservas()
            ->whereIn('negocio_id', $businessIds !== [] ? $businessIds : [0])
            ->exists();

        $hasForbiddenReservations = $cliente->reservas()
            ->whereNotIn('negocio_id', $businessIds !== [] ? $businessIds : [0])
            ->exists();

        return ! $hasForbiddenReservations && ($hasAllowedReservations || ! $cliente->reservas()->exists());
    }
}

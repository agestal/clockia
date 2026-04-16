<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\AdminAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait InteractsWithAdminAccess
{
    protected function adminAccess(): AdminAccess
    {
        return app(AdminAccess::class);
    }

    /**
     * @return list<int>|null
     */
    protected function accessibleBusinessIds(Request $request): ?array
    {
        return $this->adminAccess()->accessibleBusinessIds($request->user());
    }

    protected function scopeAccessibleBusinesses(Builder $query, Request $request, string $column = 'negocio_id'): Builder
    {
        return $this->adminAccess()->scopeBusinesses($query, $request->user(), $column);
    }

    protected function scopeAccessibleBusinessRelation(
        Builder $query,
        Request $request,
        string $relation,
        string $column = 'negocio_id'
    ): Builder {
        return $this->adminAccess()->scopeBusinessRelation($query, $request->user(), $relation, $column);
    }

    protected function scopeAccessibleClients(Builder $query, Request $request): Builder
    {
        return $this->adminAccess()->scopeClients($query, $request->user());
    }

    protected function abortUnlessBusinessAccessible(Request $request, ?int $businessId): void
    {
        abort_unless(
            $this->adminAccess()->canAccessBusinessId($request->user(), $businessId),
            Response::HTTP_FORBIDDEN,
            'No tienes permisos para usar ese negocio.'
        );
    }

    protected function abortUnlessModelAccessible(Request $request, string $modelClass, ?int $modelId): void
    {
        if ($modelId === null) {
            return;
        }

        $model = $modelClass::query()->find($modelId);

        abort_unless(
            $model && $this->adminAccess()->canAccessModel($request->user(), $model),
            Response::HTTP_FORBIDDEN,
            'No tienes permisos para usar ese recurso.'
        );
    }
}

<?php

namespace App\Services\Reservations;

use App\Models\Negocio;
use App\Models\Recurso;
use App\Models\RecursoCombinacion;
use App\Models\Servicio;
use Illuminate\Support\Collection;

class ResourceCombinationService
{
    /**
     * Find valid resource combinations for a service on a given date.
     *
     * Returns a collection of arrays, each representing a valid combination:
     * [
     *   'recursos' => Collection<Recurso>,
     *   'capacidad_total' => int,
     *   'numero_recursos' => int,
     * ]
     *
     * Results are sorted by: fewer resources first, then less excess capacity.
     */
    public function buscarCombinacionesValidas(
        Servicio $servicio,
        int $numeroPersonas,
        ?Collection $recursosDisponibles = null,
    ): Collection {
        $negocio = $servicio->negocio ?? Negocio::find($servicio->negocio_id);

        if (! $negocio) {
            return collect();
        }

        $maxCombinables = $negocio->maxRecursosCombinablesEfectivo();

        if ($maxCombinables <= 1) {
            return collect();
        }

        $candidatos = ($recursosDisponibles ?? $servicio->recursos()->activos()->get())
            ->filter(fn (Recurso $r) => $r->combinable && $r->capacidad !== null);

        if ($candidatos->count() < 2) {
            return collect();
        }

        $grafo = $this->construirGrafo($candidatos);
        $combinaciones = collect();

        // Generate pairs (size 2)
        $combinaciones = $combinaciones->merge(
            $this->buscarPares($candidatos, $grafo, $numeroPersonas)
        );

        // Generate triples (size 3) only if max allows it
        if ($maxCombinables >= 3) {
            $combinaciones = $combinaciones->merge(
                $this->buscarTriples($candidatos, $grafo, $numeroPersonas)
            );
        }

        return $combinaciones
            ->sortBy([
                ['numero_recursos', 'asc'],
                ['exceso', 'asc'],
            ])
            ->values();
    }

    /**
     * Build adjacency map from recurso_combinaciones.
     * Returns: [recurso_id => [combinable_id, ...]]
     */
    private function construirGrafo(Collection $candidatos): array
    {
        $ids = $candidatos->pluck('id')->all();

        $relaciones = RecursoCombinacion::query()
            ->whereIn('recurso_id', $ids)
            ->whereIn('recurso_combinado_id', $ids)
            ->get(['recurso_id', 'recurso_combinado_id']);

        $grafo = [];

        foreach ($relaciones as $rel) {
            $grafo[$rel->recurso_id][] = $rel->recurso_combinado_id;
        }

        return $grafo;
    }

    private function buscarPares(Collection $candidatos, array $grafo, int $personas): Collection
    {
        $resultados = collect();
        $indexado = $candidatos->keyBy('id');
        $vistos = [];

        foreach ($candidatos as $a) {
            $vecinos = $grafo[$a->id] ?? [];

            foreach ($vecinos as $bId) {
                if (! isset($indexado[$bId])) {
                    continue;
                }

                $clave = $this->claveCanonica([$a->id, $bId]);
                if (isset($vistos[$clave])) {
                    continue;
                }

                $vistos[$clave] = true;

                $b = $indexado[$bId];
                $capTotal = ($a->capacidad ?? 0) + ($b->capacidad ?? 0);

                if ($capTotal < $personas) {
                    continue;
                }

                $resultados->push([
                    'recursos' => collect([$a, $b]),
                    'recurso_ids' => [$a->id, $b->id],
                    'capacidad_total' => $capTotal,
                    'numero_recursos' => 2,
                    'exceso' => $capTotal - $personas,
                ]);
            }
        }

        return $resultados;
    }

    private function buscarTriples(Collection $candidatos, array $grafo, int $personas): Collection
    {
        $resultados = collect();
        $indexado = $candidatos->keyBy('id');
        $vistos = [];

        foreach ($candidatos as $a) {
            $vecinosA = $grafo[$a->id] ?? [];

            foreach ($vecinosA as $bId) {
                if (! isset($indexado[$bId])) {
                    continue;
                }

                $vecinosB = $grafo[$bId] ?? [];
                $comunesBC = array_intersect($vecinosA, $vecinosB);

                foreach ($comunesBC as $cId) {
                    if ($cId === $a->id || $cId === $bId || ! isset($indexado[$cId])) {
                        continue;
                    }

                    $clave = $this->claveCanonica([$a->id, $bId, $cId]);
                    if (isset($vistos[$clave])) {
                        continue;
                    }

                    $vistos[$clave] = true;

                    $b = $indexado[$bId];
                    $c = $indexado[$cId];
                    $capTotal = ($a->capacidad ?? 0) + ($b->capacidad ?? 0) + ($c->capacidad ?? 0);

                    if ($capTotal < $personas) {
                        continue;
                    }

                    $resultados->push([
                        'recursos' => collect([$a, $b, $c]),
                        'recurso_ids' => [$a->id, $bId, $cId],
                        'capacidad_total' => $capTotal,
                        'numero_recursos' => 3,
                        'exceso' => $capTotal - $personas,
                    ]);
                }
            }
        }

        return $resultados;
    }

    private function claveCanonica(array $ids): string
    {
        sort($ids);

        return implode('-', $ids);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Blocks\IndexBlockRequest;
use App\Http\Requests\Api\V1\Blocks\StoreBlockRequest;
use App\Http\Requests\Api\V1\Blocks\UpdateBlockRequest;
use App\Http\Resources\Api\V1\BlockResource;
use App\Models\Bloqueo;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends ApiController
{
    #[OA\Get(
        path: '/api/v1/businesses/{business}/blocks',
        operationId: 'listBusinessBlocks',
        tags: ['Blocks'],
        summary: 'List configured blocks for a business',
        description: 'Returns configured resource blocks scoped to the business in the URL.',
        security: [['passport' => ['blocks:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'filter[resource_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 3)),
            new OA\Parameter(name: 'filter[date]', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-04-15')),
            new OA\Parameter(name: 'filter[block_type_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 3)),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: 'Allowed values: date, -date, start_time, -start_time, created_at, -created_at', schema: new OA\Schema(type: 'string', example: '-date')),
            new OA\Parameter(name: 'page[size]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 15)),
            new OA\Parameter(name: 'page[number]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated blocks', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedBlockCollection')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function index(IndexBlockRequest $request, Negocio $business): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $filters = $validated['filter'] ?? [];
        $sort = $validated['sort'] ?? '-date';
        $perPage = min(max((int) data_get($validated, 'page.size', 15), 1), 100);
        $pageNumber = max((int) data_get($validated, 'page.number', 1), 1);

        $sortMap = [
            'date' => 'fecha',
            '-date' => 'fecha',
            'start_time' => 'hora_inicio',
            '-start_time' => 'hora_inicio',
            'created_at' => 'created_at',
            '-created_at' => 'created_at',
        ];

        $sortColumn = $sortMap[$sort] ?? 'fecha';
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';

        $blocks = Bloqueo::query()
            ->where(function ($query) use ($business) {
                $query->whereHas('recurso', fn ($inner) => $inner->where('negocio_id', $business->id))
                    ->orWhereHas('servicio', fn ($inner) => $inner->where('negocio_id', $business->id))
                    ->orWhere('negocio_id', $business->id);
            })
            ->with(['recurso', 'tipoBloqueo', 'negocio', 'servicio'])
            ->when(filled($filters['resource_id'] ?? null), function ($query) use ($filters) {
                $query->where('recurso_id', (int) $filters['resource_id']);
            })
            ->when(filled($filters['service_id'] ?? null), function ($query) use ($filters) {
                $query->where('servicio_id', (int) $filters['service_id']);
            })
            ->when(filled($filters['date'] ?? null), function ($query) use ($filters) {
                $query->whereDate('fecha', $filters['date']);
            })
            ->when(filled($filters['block_type_id'] ?? null), function ($query) use ($filters) {
                $query->where('tipo_bloqueo_id', (int) $filters['block_type_id']);
            })
            ->orderBy($sortColumn, $sortDirection)
            ->when($sortColumn !== 'hora_inicio', fn ($query) => $query->orderByRaw('hora_inicio asc nulls first'))
            ->paginate($perPage, ['*'], 'page', $pageNumber)
            ->withQueryString();

        return BlockResource::collection($blocks);
    }

    #[OA\Post(
        path: '/api/v1/businesses/{business}/blocks',
        operationId: 'createBusinessBlock',
        tags: ['Blocks'],
        summary: 'Create a configured block',
        security: [['passport' => ['blocks:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/BlockUpsertRequest',
                example: [
                    'resource_id' => 3,
                    'block_type_id' => 3,
                    'date' => '2026-04-15',
                    'start_time' => '18:00',
                    'end_time' => '20:00',
                    'reason' => 'Private event setup',
                    'is_recurring' => false,
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created block', content: new OA\JsonContent(ref: '#/components/schemas/Block')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreBlockRequest $request, Negocio $business): JsonResponse
    {
        $block = Bloqueo::create($request->blockAttributes());
        $block->load(['recurso', 'tipoBloqueo', 'negocio', 'servicio']);

        return (new BlockResource($block))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/v1/businesses/{business}/blocks/{block}',
        operationId: 'showBusinessBlock',
        tags: ['Blocks'],
        summary: 'Show a configured block',
        security: [['passport' => ['blocks:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'block', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Block detail', content: new OA\JsonContent(ref: '#/components/schemas/Block')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function show(Negocio $business, string $block): BlockResource
    {
        return new BlockResource($this->findBlockOrFail($business, $block));
    }

    #[OA\Put(
        path: '/api/v1/businesses/{business}/blocks/{block}',
        operationId: 'updateBusinessBlock',
        tags: ['Blocks'],
        summary: 'Update a configured block',
        security: [['passport' => ['blocks:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'block', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/BlockUpsertRequest',
                example: [
                    'resource_id' => 3,
                    'block_type_id' => 3,
                    'date' => '2026-04-15',
                    'start_time' => null,
                    'end_time' => null,
                    'reason' => 'Maintenance day',
                    'is_recurring' => false,
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated block', content: new OA\JsonContent(ref: '#/components/schemas/Block')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    #[OA\Patch(
        path: '/api/v1/businesses/{business}/blocks/{block}',
        operationId: 'patchBusinessBlock',
        tags: ['Blocks'],
        summary: 'Partially update a configured block',
        security: [['passport' => ['blocks:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'block', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BlockUpsertRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Updated block', content: new OA\JsonContent(ref: '#/components/schemas/Block')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateBlockRequest $request, Negocio $business, string $block): BlockResource
    {
        $blockModel = $this->findBlockOrFail($business, $block, withRelations: false);
        $blockModel->update($request->blockAttributes());
        $blockModel->load(['recurso', 'tipoBloqueo', 'negocio', 'servicio']);

        return new BlockResource($blockModel);
    }

    #[OA\Delete(
        path: '/api/v1/businesses/{business}/blocks/{block}',
        operationId: 'deleteBusinessBlock',
        tags: ['Blocks'],
        summary: 'Delete a configured block',
        security: [['passport' => ['blocks:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'block', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Block deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function destroy(Negocio $business, string $block): JsonResponse
    {
        $blockModel = $this->findBlockOrFail($business, $block, withRelations: false);
        $blockModel->delete();

        return $this->respondNoContent();
    }

    private function findBlockOrFail(Negocio $business, string $block, bool $withRelations = true): Bloqueo
    {
        $query = Bloqueo::query()
            ->whereKey($block)
            ->where(function ($builder) use ($business) {
                $builder->whereHas('recurso', fn ($inner) => $inner->where('negocio_id', $business->id))
                    ->orWhereHas('servicio', fn ($inner) => $inner->where('negocio_id', $business->id))
                    ->orWhere('negocio_id', $business->id);
            });

        if ($withRelations) {
            $query->with(['recurso', 'tipoBloqueo', 'negocio', 'servicio']);
        }

        return $query->firstOrFail();
    }
}

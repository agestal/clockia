<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Resources\IndexResourceRequest;
use App\Http\Requests\Api\V1\Resources\StoreResourceRequest;
use App\Http\Requests\Api\V1\Resources\UpdateResourceRequest;
use App\Http\Resources\Api\V1\ResourceResource;
use App\Models\Negocio;
use App\Models\Recurso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends ApiController
{
    #[OA\Get(
        path: '/api/v1/businesses/{business}/resources',
        operationId: 'listBusinessResources',
        tags: ['Resources'],
        summary: 'List resources for a business',
        description: 'Returns a paginated resource collection scoped to the business in the URL.',
        security: [['passport' => ['resources:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'filter[name]', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Terrace')),
            new OA\Parameter(name: 'filter[is_active]', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'filter[resource_type_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: 'Allowed values: name, -name, created_at, -created_at', schema: new OA\Schema(type: 'string', example: 'name')),
            new OA\Parameter(name: 'page[size]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 15)),
            new OA\Parameter(name: 'page[number]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated resources', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedResourceCollection')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function index(IndexResourceRequest $request, Negocio $business): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $filters = $validated['filter'] ?? [];
        $sort = $validated['sort'] ?? 'name';
        $perPage = min(max((int) data_get($validated, 'page.size', 15), 1), 100);
        $pageNumber = max((int) data_get($validated, 'page.number', 1), 1);

        $sortMap = [
            'name' => 'nombre',
            '-name' => 'nombre',
            'created_at' => 'created_at',
            '-created_at' => 'created_at',
        ];

        $sortColumn = $sortMap[$sort] ?? 'nombre';
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';

        $resources = $business->recursos()
            ->with(['negocio', 'tipoRecurso', 'servicios'])
            ->withCount(['disponibilidades', 'bloqueos', 'reservas'])
            ->when(filled($filters['name'] ?? null), function ($query) use ($filters) {
                $query->where('nombre', 'like', '%'.$filters['name'].'%');
            })
            ->when(($filters['is_active'] ?? null) !== null, function ($query) use ($filters) {
                $query->where('activo', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->when(filled($filters['resource_type_id'] ?? null), function ($query) use ($filters) {
                $query->where('tipo_recurso_id', (int) $filters['resource_type_id']);
            })
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $pageNumber)
            ->withQueryString();

        return ResourceResource::collection($resources);
    }

    #[OA\Post(
        path: '/api/v1/businesses/{business}/resources',
        operationId: 'createBusinessResource',
        tags: ['Resources'],
        summary: 'Create a resource for a business',
        security: [['passport' => ['resources:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ResourceUpsertRequest',
                example: [
                    'name' => 'Terrace Table 1',
                    'resource_type_id' => 1,
                    'capacity' => 4,
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created resource', content: new OA\JsonContent(ref: '#/components/schemas/Resource')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreResourceRequest $request, Negocio $business): JsonResponse
    {
        $resource = $business->recursos()->create($request->resourceAttributes());

        $resource->load(['negocio', 'tipoRecurso', 'servicios'])
            ->loadCount(['disponibilidades', 'bloqueos', 'reservas']);

        return (new ResourceResource($resource))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/v1/businesses/{business}/resources/{resource}',
        operationId: 'showBusinessResource',
        tags: ['Resources'],
        summary: 'Show a resource',
        security: [['passport' => ['resources:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'resource', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Resource detail', content: new OA\JsonContent(ref: '#/components/schemas/Resource')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function show(Negocio $business, string $resource): ResourceResource
    {
        $resource = $this->findResourceOrFail($business, $resource, withCombinables: true);

        return new ResourceResource($resource);
    }

    #[OA\Put(
        path: '/api/v1/businesses/{business}/resources/{resource}',
        operationId: 'updateBusinessResource',
        tags: ['Resources'],
        summary: 'Update a resource',
        security: [['passport' => ['resources:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'resource', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ResourceUpsertRequest',
                example: [
                    'name' => 'Terrace Table 1',
                    'resource_type_id' => 1,
                    'capacity' => 6,
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated resource', content: new OA\JsonContent(ref: '#/components/schemas/Resource')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    #[OA\Patch(
        path: '/api/v1/businesses/{business}/resources/{resource}',
        operationId: 'patchBusinessResource',
        tags: ['Resources'],
        summary: 'Partially update a resource',
        security: [['passport' => ['resources:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'resource', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ResourceUpsertRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Updated resource', content: new OA\JsonContent(ref: '#/components/schemas/Resource')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateResourceRequest $request, Negocio $business, string $resource): ResourceResource
    {
        $resourceModel = $this->findResourceOrFail($business, $resource);
        $resourceModel->update($request->resourceAttributes());

        $resourceModel->load(['negocio', 'tipoRecurso', 'servicios'])
            ->loadCount(['disponibilidades', 'bloqueos', 'reservas']);

        return new ResourceResource($resourceModel);
    }

    #[OA\Delete(
        path: '/api/v1/businesses/{business}/resources/{resource}',
        operationId: 'deleteBusinessResource',
        tags: ['Resources'],
        summary: 'Delete a resource',
        security: [['passport' => ['resources:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'resource', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 3)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Resource deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 409, description: 'Conflict', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError')),
        ]
    )]
    public function destroy(Negocio $business, string $resource): JsonResponse
    {
        $resourceModel = $this->findResourceOrFail($business, $resource, withServices: false);
        $resourceModel->loadCount(['disponibilidades', 'bloqueos', 'reservas', 'servicioRecursos']);

        if ($resourceModel->disponibilidades_count > 0 || $resourceModel->bloqueos_count > 0 || $resourceModel->reservas_count > 0) {
            return response()->json([
                'message' => 'This resource cannot be deleted because it has related availabilities, blocks or reservations.',
            ], Response::HTTP_CONFLICT);
        }

        DB::transaction(function () use ($resourceModel) {
            if ($resourceModel->servicioRecursos_count > 0) {
                $resourceModel->servicios()->detach();
            }

            $resourceModel->delete();
        });

        return $this->respondNoContent();
    }

    private function findResourceOrFail(Negocio $business, string $resource, bool $withServices = true, bool $withCombinables = false): Recurso
    {
        $query = $business->recursos()
            ->whereKey($resource)
            ->with(['negocio', 'tipoRecurso'])
            ->withCount(['disponibilidades', 'bloqueos', 'reservas']);

        if ($withServices) {
            $query->with(['servicios' => fn ($builder) => $builder->orderBy('nombre')]);
        }

        if ($withCombinables) {
            $query->with(['recursosCombinables.recursoCombinado']);
        }

        return $query->firstOrFail();
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Services\IndexServiceRequest;
use App\Http\Requests\Api\V1\Services\StoreServiceRequest;
use App\Http\Requests\Api\V1\Services\UpdateServiceRequest;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\Negocio;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends ApiController
{
    #[OA\Get(
        path: '/api/v1/businesses/{business}/services',
        operationId: 'listBusinessServices',
        tags: ['Services'],
        summary: 'List services for a business',
        description: 'Returns a paginated service collection scoped to the business in the URL.',
        security: [['passport' => ['services:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, description: 'Business identifier', schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'filter[name]', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Dinner')),
            new OA\Parameter(name: 'filter[is_active]', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'filter[requires_payment]', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'filter[price_type_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 2)),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: 'Allowed values: name, -name, created_at, -created_at', schema: new OA\Schema(type: 'string', example: '-created_at')),
            new OA\Parameter(name: 'page[size]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 15)),
            new OA\Parameter(name: 'page[number]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated services', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedServiceCollection')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function index(IndexServiceRequest $request, Negocio $business): AnonymousResourceCollection
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

        $services = $business->servicios()
            ->with(['negocio', 'tipoPrecio', 'recursos'])
            ->withCount(['recursos', 'reservas'])
            ->when(filled($filters['name'] ?? null), function ($query) use ($filters) {
                $query->where('nombre', 'like', '%'.$filters['name'].'%');
            })
            ->when(($filters['is_active'] ?? null) !== null, function ($query) use ($filters) {
                $query->where('activo', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->when(($filters['requires_payment'] ?? null) !== null, function ($query) use ($filters) {
                $query->where('requiere_pago', filter_var($filters['requires_payment'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->when(filled($filters['price_type_id'] ?? null), function ($query) use ($filters) {
                $query->where('tipo_precio_id', (int) $filters['price_type_id']);
            })
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $pageNumber)
            ->withQueryString();

        return ServiceResource::collection($services);
    }

    #[OA\Post(
        path: '/api/v1/businesses/{business}/services',
        operationId: 'createBusinessService',
        tags: ['Services'],
        summary: 'Create a service for a business',
        security: [['passport' => ['services:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, description: 'Business identifier', schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ServiceUpsertRequest',
                example: [
                    'name' => 'Dinner',
                    'description' => 'Evening table reservation.',
                    'duration_minutes' => 120,
                    'base_price' => 35.00,
                    'price_type_id' => 2,
                    'requires_payment' => true,
                    'is_active' => true,
                    'resource_ids' => [1, 2, 3],
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created service', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreServiceRequest $request, Negocio $business): JsonResponse
    {
        $service = DB::transaction(function () use ($request, $business) {
            $service = $business->servicios()->create($request->serviceAttributes());
            $service->recursos()->sync($request->resourceIds());

            return $service;
        });

        $service->load(['negocio', 'tipoPrecio', 'recursos'])
            ->loadCount(['recursos', 'reservas']);

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/v1/businesses/{business}/services/{service}',
        operationId: 'showBusinessService',
        tags: ['Services'],
        summary: 'Show a service',
        security: [['passport' => ['services:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Service detail', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function show(Negocio $business, string $service): ServiceResource
    {
        $service = $this->findServiceOrFail($business, $service, true);

        return new ServiceResource($service);
    }

    #[OA\Put(
        path: '/api/v1/businesses/{business}/services/{service}',
        operationId: 'updateBusinessService',
        tags: ['Services'],
        summary: 'Update a service',
        security: [['passport' => ['services:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ServiceUpsertRequest',
                example: [
                    'name' => 'Dinner',
                    'description' => 'Updated evening service.',
                    'duration_minutes' => 135,
                    'base_price' => 39.50,
                    'price_type_id' => 2,
                    'requires_payment' => true,
                    'is_active' => true,
                    'resource_ids' => [1, 2, 5],
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated service', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    #[OA\Patch(
        path: '/api/v1/businesses/{business}/services/{service}',
        operationId: 'patchBusinessService',
        tags: ['Services'],
        summary: 'Partially update a service',
        security: [['passport' => ['services:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ServiceUpsertRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated service', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateServiceRequest $request, Negocio $business, string $service): ServiceResource
    {
        $serviceModel = $this->findServiceOrFail($business, $service);

        DB::transaction(function () use ($request, $serviceModel) {
            $serviceModel->update($request->serviceAttributes());
            $serviceModel->recursos()->sync($request->resourceIds());
        });

        $serviceModel->load(['negocio', 'tipoPrecio', 'recursos'])
            ->loadCount(['recursos', 'reservas']);

        return new ServiceResource($serviceModel);
    }

    #[OA\Delete(
        path: '/api/v1/businesses/{business}/services/{service}',
        operationId: 'deleteBusinessService',
        tags: ['Services'],
        summary: 'Delete a service',
        security: [['passport' => ['services:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'service', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Service deleted'),
            new OA\Response(
                response: 409,
                description: 'Conflict',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This service cannot be deleted because it has related reservations.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function destroy(Negocio $business, string $service): JsonResponse
    {
        $serviceModel = $this->findServiceOrFail($business, $service);
        $serviceModel->loadCount(['reservas', 'servicioRecursos']);

        if ($serviceModel->reservas_count > 0) {
            return response()->json([
                'message' => 'This service cannot be deleted because it has related reservations.',
            ], Response::HTTP_CONFLICT);
        }

        DB::transaction(function () use ($serviceModel) {
            if ($serviceModel->servicioRecursos_count > 0) {
                $serviceModel->recursos()->detach();
            }

            $serviceModel->delete();
        });

        return $this->respondNoContent();
    }

    private function findServiceOrFail(Negocio $business, string $service, bool $forShow = false): Servicio
    {
        $query = $business->servicios()
            ->whereKey($service)
            ->with(['negocio', 'tipoPrecio', 'recursos'])
            ->withCount(['recursos', 'reservas']);

        return $query->firstOrFail();
    }
}

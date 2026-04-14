<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Availabilities\IndexAvailabilityRequest;
use App\Http\Requests\Api\V1\Availabilities\StoreAvailabilityRequest;
use App\Http\Requests\Api\V1\Availabilities\UpdateAvailabilityRequest;
use App\Http\Resources\Api\V1\AvailabilityResource;
use App\Models\Disponibilidad;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AvailabilityController extends ApiController
{
    #[OA\Get(
        path: '/api/v1/businesses/{business}/availabilities',
        operationId: 'listBusinessAvailabilities',
        tags: ['Availabilities'],
        summary: 'List weekly availability configuration for a business',
        description: 'Returns weekly availability rules configured for resources that belong to the business.',
        security: [['passport' => ['availabilities:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'filter[resource_id]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 3)),
            new OA\Parameter(name: 'filter[weekday]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [0,1,2,3,4,5,6], example: 2)),
            new OA\Parameter(name: 'filter[is_active]', in: 'query', required: false, schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: 'Allowed values: weekday, -weekday, start_time, -start_time, created_at, -created_at', schema: new OA\Schema(type: 'string', example: 'weekday')),
            new OA\Parameter(name: 'page[size]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 15)),
            new OA\Parameter(name: 'page[number]', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated availabilities', content: new OA\JsonContent(ref: '#/components/schemas/PaginatedAvailabilityCollection')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function index(IndexAvailabilityRequest $request, Negocio $business): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $filters = $validated['filter'] ?? [];
        $sort = $validated['sort'] ?? 'weekday';
        $perPage = min(max((int) data_get($validated, 'page.size', 15), 1), 100);
        $pageNumber = max((int) data_get($validated, 'page.number', 1), 1);

        $sortMap = [
            'weekday' => 'dia_semana',
            '-weekday' => 'dia_semana',
            'start_time' => 'hora_inicio',
            '-start_time' => 'hora_inicio',
            'created_at' => 'created_at',
            '-created_at' => 'created_at',
        ];

        $sortColumn = $sortMap[$sort] ?? 'dia_semana';
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';

        $availabilities = Disponibilidad::query()
            ->whereHas('recurso', fn ($query) => $query->where('negocio_id', $business->id))
            ->with('recurso')
            ->when(filled($filters['resource_id'] ?? null), function ($query) use ($filters) {
                $query->where('recurso_id', (int) $filters['resource_id']);
            })
            ->when(($filters['weekday'] ?? null) !== null, function ($query) use ($filters) {
                $query->where('dia_semana', (int) $filters['weekday']);
            })
            ->when(($filters['is_active'] ?? null) !== null, function ($query) use ($filters) {
                $query->where('activo', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->orderBy($sortColumn, $sortDirection)
            ->when($sortColumn !== 'hora_inicio', fn ($query) => $query->orderBy('hora_inicio'))
            ->paginate($perPage, ['*'], 'page', $pageNumber)
            ->withQueryString();

        return AvailabilityResource::collection($availabilities);
    }

    #[OA\Post(
        path: '/api/v1/businesses/{business}/availabilities',
        operationId: 'createBusinessAvailability',
        tags: ['Availabilities'],
        summary: 'Create weekly availability configuration',
        security: [['passport' => ['availabilities:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/AvailabilityUpsertRequest',
                example: [
                    'resource_id' => 3,
                    'weekday' => 2,
                    'start_time' => '13:00',
                    'end_time' => '16:00',
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created availability', content: new OA\JsonContent(ref: '#/components/schemas/Availability')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreAvailabilityRequest $request, Negocio $business): JsonResponse
    {
        $availability = Disponibilidad::create($request->availabilityAttributes());

        $availability->load('recurso');

        return (new AvailabilityResource($availability))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/api/v1/businesses/{business}/availabilities/{availability}',
        operationId: 'showBusinessAvailability',
        tags: ['Availabilities'],
        summary: 'Show weekly availability configuration',
        security: [['passport' => ['availabilities:read']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'availability', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Availability detail', content: new OA\JsonContent(ref: '#/components/schemas/Availability')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function show(Negocio $business, string $availability): AvailabilityResource
    {
        return new AvailabilityResource($this->findAvailabilityOrFail($business, $availability));
    }

    #[OA\Put(
        path: '/api/v1/businesses/{business}/availabilities/{availability}',
        operationId: 'updateBusinessAvailability',
        tags: ['Availabilities'],
        summary: 'Update weekly availability configuration',
        security: [['passport' => ['availabilities:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'availability', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/AvailabilityUpsertRequest',
                example: [
                    'resource_id' => 3,
                    'weekday' => 2,
                    'start_time' => '20:00',
                    'end_time' => '23:30',
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated availability', content: new OA\JsonContent(ref: '#/components/schemas/Availability')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    #[OA\Patch(
        path: '/api/v1/businesses/{business}/availabilities/{availability}',
        operationId: 'patchBusinessAvailability',
        tags: ['Availabilities'],
        summary: 'Partially update weekly availability configuration',
        security: [['passport' => ['availabilities:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'availability', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AvailabilityUpsertRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Updated availability', content: new OA\JsonContent(ref: '#/components/schemas/Availability')),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateAvailabilityRequest $request, Negocio $business, string $availability): AvailabilityResource
    {
        $availabilityModel = $this->findAvailabilityOrFail($business, $availability);
        $availabilityModel->update($request->availabilityAttributes());
        $availabilityModel->load('recurso');

        return new AvailabilityResource($availabilityModel);
    }

    #[OA\Delete(
        path: '/api/v1/businesses/{business}/availabilities/{availability}',
        operationId: 'deleteBusinessAvailability',
        tags: ['Availabilities'],
        summary: 'Delete weekly availability configuration',
        security: [['passport' => ['availabilities:write']]],
        parameters: [
            new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'availability', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Availability deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
            new OA\Response(response: 404, description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        ]
    )]
    public function destroy(Negocio $business, string $availability): JsonResponse
    {
        $availabilityModel = $this->findAvailabilityOrFail($business, $availability, withResource: false);
        $availabilityModel->delete();

        return $this->respondNoContent();
    }

    private function findAvailabilityOrFail(Negocio $business, string $availability, bool $withResource = true): Disponibilidad
    {
        $query = Disponibilidad::query()
            ->whereKey($availability)
            ->whereHas('recurso', fn ($builder) => $builder->where('negocio_id', $business->id));

        if ($withResource) {
            $query->with('recurso');
        }

        return $query->firstOrFail();
    }
}

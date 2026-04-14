<?php

namespace App\OpenApi\V1;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Clockia API v1',
    description: 'Versioned API for business-scoped reservation operations.'
)]
#[OA\Server(
    url: '/',
    description: 'Application base URL'
)]
#[OA\Tag(
    name: 'Services',
    description: 'Business-scoped service management endpoints.'
)]
#[OA\Tag(
    name: 'Resources',
    description: 'Business-scoped operational resources.'
)]
#[OA\Tag(
    name: 'Availabilities',
    description: 'Weekly availability configuration for business resources.'
)]
#[OA\Tag(
    name: 'Blocks',
    description: 'Configured blocks for business resources.'
)]
#[OA\Schema(
    schema: 'BusinessSummary',
    required: ['id', 'name', 'is_active'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Restaurante Marea Alta'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'publicDescription', type: 'string', nullable: true, example: 'A cozy seaside restaurant.'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: '123 Ocean Drive'),
        new OA\Property(property: 'website', type: 'string', nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'cancellationPolicy', type: 'string', nullable: true, example: 'Free cancellation up to 24h before.'),
        new OA\Property(property: 'minCancellationHours', type: 'integer', nullable: true, example: 24),
        new OA\Property(property: 'allowsModification', type: 'boolean', example: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PriceTypeSummary',
    required: ['id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'name', type: 'string', example: 'Por persona'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Precio calculado por cada persona.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'OperationalResourceSummary',
    required: ['id', 'name', 'is_active'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 3),
        new OA\Property(property: 'name', type: 'string', example: 'Mesa interior 4'),
        new OA\Property(property: 'capacity', type: 'integer', nullable: true, example: 4),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ResourceTypeSummary',
    required: ['id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Mesa'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Recurso físico para atención o servicio.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ServiceSummary',
    required: ['id', 'name', 'duration_minutes', 'base_price', 'requires_payment', 'is_active'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Dinner'),
        new OA\Property(property: 'duration_minutes', type: 'integer', example: 120),
        new OA\Property(property: 'base_price', type: 'string', example: '35.00'),
        new OA\Property(property: 'requires_payment', type: 'boolean', example: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'BlockTypeSummary',
    required: ['id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 3),
        new OA\Property(property: 'name', type: 'string', example: 'Maintenance'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Temporary operational block.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'Service',
    required: ['id', 'name', 'duration_minutes', 'base_price', 'requires_payment', 'is_active', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'business', ref: '#/components/schemas/BusinessSummary'),
        new OA\Property(property: 'name', type: 'string', example: 'Dinner'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Evening table reservation.'),
        new OA\Property(property: 'duration_minutes', type: 'integer', example: 120),
        new OA\Property(property: 'base_price', type: 'string', example: '35.00'),
        new OA\Property(property: 'price_type', ref: '#/components/schemas/PriceTypeSummary'),
        new OA\Property(property: 'requires_payment', type: 'boolean', example: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(
            property: 'resources',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OperationalResourceSummary')
        ),
        new OA\Property(property: 'resources_count', type: 'integer', nullable: true, example: 6),
        new OA\Property(property: 'reservations_count', type: 'integer', nullable: true, example: 4),
        new OA\Property(property: 'publicNotes', type: 'string', nullable: true, example: 'Outdoor seating available.'),
        new OA\Property(property: 'priorInstructions', type: 'string', nullable: true, example: 'Please arrive 10 minutes early.'),
        new OA\Property(property: 'requiredDocumentation', type: 'string', nullable: true, example: 'Valid ID required.'),
        new OA\Property(property: 'minCancellationHours', type: 'integer', nullable: true, minimum: 0, example: 24),
        new OA\Property(property: 'isRefundable', type: 'boolean', example: true),
        new OA\Property(property: 'depositPercentage', type: 'number', format: 'float', nullable: true, minimum: 0, maximum: 100, example: 20.00),
        new OA\Property(property: 'pricePerTimeUnit', type: 'boolean', example: false),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T12:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T12:30:00Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ServiceUpsertRequest',
    required: ['name', 'duration_minutes', 'base_price', 'price_type_id', 'requires_payment', 'is_active'],
    properties: [
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 255, example: 'Dinner'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Evening table reservation.'),
        new OA\Property(property: 'duration_minutes', type: 'integer', minimum: 1, example: 120),
        new OA\Property(property: 'base_price', type: 'number', format: 'float', minimum: 0, example: 35.00),
        new OA\Property(property: 'price_type_id', type: 'integer', example: 2),
        new OA\Property(property: 'requires_payment', type: 'boolean', example: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(
            property: 'resource_ids',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2, 3]
        ),
        new OA\Property(property: 'publicNotes', type: 'string', nullable: true),
        new OA\Property(property: 'priorInstructions', type: 'string', nullable: true),
        new OA\Property(property: 'requiredDocumentation', type: 'string', nullable: true),
        new OA\Property(property: 'minCancellationHours', type: 'integer', nullable: true, minimum: 0),
        new OA\Property(property: 'isRefundable', type: 'boolean'),
        new OA\Property(property: 'depositPercentage', type: 'number', format: 'float', nullable: true, minimum: 0, maximum: 100),
        new OA\Property(property: 'pricePerTimeUnit', type: 'boolean'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ValidationError',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['name' => ['The name field is required.']]
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'MessageError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ConflictError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'This resource cannot be deleted because it has related availabilities, blocks or reservations.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaginatedServiceCollection',
    required: ['data', 'links', 'meta'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Service')
        ),
        new OA\Property(property: 'links', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'Resource',
    required: ['id', 'name', 'is_active', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'business', ref: '#/components/schemas/BusinessSummary'),
        new OA\Property(property: 'name', type: 'string', example: 'Terrace Table 1'),
        new OA\Property(property: 'resource_type', ref: '#/components/schemas/ResourceTypeSummary'),
        new OA\Property(property: 'capacity', type: 'integer', nullable: true, example: 4),
        new OA\Property(property: 'minCapacity', type: 'integer', nullable: true, minimum: 1, example: 1),
        new OA\Property(property: 'isCombinable', type: 'boolean', example: false),
        new OA\Property(property: 'publicNotes', type: 'string', nullable: true, example: 'Near the window.'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(
            property: 'services',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ServiceSummary')
        ),
        new OA\Property(property: 'availabilities_count', type: 'integer', nullable: true, example: 12),
        new OA\Property(property: 'blocks_count', type: 'integer', nullable: true, example: 2),
        new OA\Property(property: 'reservations_count', type: 'integer', nullable: true, example: 5),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T12:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T12:30:00Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ResourceUpsertRequest',
    required: ['name', 'resource_type_id', 'is_active'],
    properties: [
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 255, example: 'Terrace Table 1'),
        new OA\Property(property: 'resource_type_id', type: 'integer', example: 1),
        new OA\Property(property: 'capacity', type: 'integer', nullable: true, minimum: 1, example: 4),
        new OA\Property(property: 'minCapacity', type: 'integer', nullable: true, minimum: 1),
        new OA\Property(property: 'isCombinable', type: 'boolean'),
        new OA\Property(property: 'publicNotes', type: 'string', nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaginatedResourceCollection',
    required: ['data', 'links', 'meta'],
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Resource')),
        new OA\Property(property: 'links', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'Availability',
    required: ['id', 'weekday', 'start_time', 'end_time', 'is_active', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'resource', ref: '#/components/schemas/OperationalResourceSummary'),
        new OA\Property(property: 'weekday', type: 'integer', example: 2),
        new OA\Property(property: 'start_time', type: 'string', example: '13:00'),
        new OA\Property(property: 'end_time', type: 'string', example: '16:00'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'shiftName', type: 'string', nullable: true, maxLength: 255, example: 'Morning'),
        new OA\Property(property: 'bufferMinutes', type: 'integer', nullable: true, minimum: 0, example: 15),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T12:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T12:30:00Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'AvailabilityUpsertRequest',
    required: ['resource_id', 'weekday', 'start_time', 'end_time', 'is_active'],
    properties: [
        new OA\Property(property: 'resource_id', type: 'integer', example: 3),
        new OA\Property(property: 'weekday', type: 'integer', enum: [0,1,2,3,4,5,6], example: 2),
        new OA\Property(property: 'start_time', type: 'string', example: '13:00'),
        new OA\Property(property: 'end_time', type: 'string', example: '16:00'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'shiftName', type: 'string', nullable: true, maxLength: 255),
        new OA\Property(property: 'bufferMinutes', type: 'integer', nullable: true, minimum: 0),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaginatedAvailabilityCollection',
    required: ['data', 'links', 'meta'],
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Availability')),
        new OA\Property(property: 'links', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'Block',
    required: ['id', 'is_full_day', 'is_range', 'is_recurring', 'is_business_wide', 'is_active', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'business', ref: '#/components/schemas/BusinessSummary'),
        new OA\Property(property: 'resource', ref: '#/components/schemas/OperationalResourceSummary'),
        new OA\Property(property: 'block_type', ref: '#/components/schemas/BlockTypeSummary'),
        new OA\Property(property: 'date', type: 'string', format: 'date', nullable: true, example: '2026-04-15'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true, example: '2026-04-15'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2026-04-20'),
        new OA\Property(property: 'is_recurring', type: 'boolean', example: false),
        new OA\Property(property: 'weekday', type: 'integer', nullable: true, minimum: 0, maximum: 6, example: 1),
        new OA\Property(property: 'start_time', type: 'string', nullable: true, example: '18:00'),
        new OA\Property(property: 'end_time', type: 'string', nullable: true, example: '20:00'),
        new OA\Property(property: 'reason', type: 'string', nullable: true, example: 'Private event setup'),
        new OA\Property(property: 'is_full_day', type: 'boolean', example: false),
        new OA\Property(property: 'is_range', type: 'boolean', example: false),
        new OA\Property(property: 'is_business_wide', type: 'boolean', example: false),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T12:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T12:30:00Z'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'BlockUpsertRequest',
    required: ['block_type_id', 'is_recurring', 'is_active'],
    properties: [
        new OA\Property(property: 'resource_id', type: 'integer', nullable: true, example: 3, description: 'If null the block applies to the entire business.'),
        new OA\Property(property: 'block_type_id', type: 'integer', example: 3),
        new OA\Property(property: 'date', type: 'string', format: 'date', nullable: true, example: '2026-04-15', description: 'Punctual date. Mutually exclusive with start_date/end_date when is_recurring is false.'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true, example: '2026-04-15'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2026-04-20'),
        new OA\Property(property: 'is_recurring', type: 'boolean', example: false),
        new OA\Property(property: 'weekday', type: 'integer', nullable: true, minimum: 0, maximum: 6, example: 1, description: 'Required when is_recurring is true.'),
        new OA\Property(property: 'start_time', type: 'string', nullable: true, example: '18:00'),
        new OA\Property(property: 'end_time', type: 'string', nullable: true, example: '20:00'),
        new OA\Property(property: 'reason', type: 'string', nullable: true, maxLength: 255, example: 'Private event setup'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaginatedBlockCollection',
    required: ['data', 'links', 'meta'],
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Block')),
        new OA\Property(property: 'links', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
    type: 'object'
)]
class OpenApiSpec
{
}

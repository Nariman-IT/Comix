<?php

namespace App\Comics\Auth\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Nariman'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nariman@example.com'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-22T12:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-22T12:30:00Z'),
    ]
)]

class UserResource
{
}
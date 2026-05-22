<?php

namespace App\Comics\Auth\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'ValidationError',
    description: 'Ошибка валидации',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'errors',
                type: 'object',
                additionalProperties: new OA\AdditionalProperties(
                    type: 'array',
                    items: new OA\Items(type: 'string')
                ),
                example: [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field must be at least 6 characters.'],
                    'name' => ['The name field is required.'],
                ]
            ),
        ]
    )
)]

class ValidationError
{
}
<?php

namespace App\Comics\Auth\Swagger\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/auth/login',
    tags: ['Auth'],
    summary: 'Вход в систему',
    requestBody: new OA\RequestBody(
        required: true,
        description: 'Данные для входа',
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nariman@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Успешный вход',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Неверные учетные данные',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Unauthorized'),
                ]
            )
        ),
        new OA\Response(
            response: 422,
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
                        ]
                    ),
                ]
            )
        ),
    ]
)]


class Login
{
    
}
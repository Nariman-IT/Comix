<?php

namespace App\Comics\Auth\Swagger\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/auth/register',
    tags: ['Auth'],
    summary: 'Регистрация нового пользователя',
    requestBody: new OA\RequestBody(
        required: true,
        description: 'Данные для регистрации',
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Nariman'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nariman@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'secret123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password',  minLength: 6, example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Успешная регистрация',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                    new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                ]
            )
        ),
        new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
    ]
)]

class Register
{
}
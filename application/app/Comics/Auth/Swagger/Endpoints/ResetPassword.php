<?php

namespace App\Comics\Auth\Swagger\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/auth/reset-password',
    tags: ['Auth'],
    summary: 'Сброс пароля',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'token', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nariman@example.com'),
                new OA\Property(property: 'token', type: 'string', example: 'abc123...'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'newsecret123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newsecret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Пароль изменён',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Password has been reset.'),
                ]
            )
        ),
        new OA\Response(
            response: 400,
            description: 'Неверный токен',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Invalid token.'),
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
                            'token' => ['The token field is required.'],
                            'password' => ['The password field must be at least 6 characters.'],
                        ]
                    ),
                ]
            )
        ),
    ]
)]

class ResetPassword
{
}
<?php

namespace App\Comics\Auth\Swagger\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/auth/forgot-password',
    tags: ['Auth'],
    summary: 'Запрос на сброс пароля',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'nariman@example.com'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Инструкция отправлена (или нет — для безопасности)',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'If the email exists, a reset token has been sent.'
                    ),
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
                        ]
                    ),
                ]
            )
        ),
    ]
)]

class ForgotPassword
{
}
<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Comics API',
    version: '1.0.0',
    description: 'API для сайта комиксов',
)]

#[OA\Server(
    url: 'http://localhost',
    description: 'Dev сервер'
)]

#[OA\Server(
    url: 'http://localhost',
    description: 'Production сервер'
)]

#[OA\Tag(name: 'Auth', description: 'Аутентификация и сброс пароля')]

class Info
{

}
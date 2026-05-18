<?php

namespace App\Comix\Auth\Dto;

final readonly class ResetPasswordDto
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
    ) {}
}
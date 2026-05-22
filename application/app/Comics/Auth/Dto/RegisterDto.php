<?php

namespace App\Comics\Auth\Dto;

final readonly class RegisterDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
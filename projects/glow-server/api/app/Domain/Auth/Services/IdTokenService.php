<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class IdTokenService
{
    private string $privateKey;
    private string $publicKey;

    public function __construct(?string $privateKey = null, ?string $publicKey = null)
    {
        $this->privateKey = $privateKey ?? config('encryption.jwt_private_key');
        $this->publicKey = $publicKey ?? config('encryption.jwt_public_key');
    }

    public function create(string $uuid): string
    {
        return JWT::encode(['uuid' => $uuid], $this->privateKey, 'RS256');
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function getUuid(string $tokenString): string
    {
        $decoded = (array) JWT::decode($tokenString, new Key($this->publicKey, 'RS256'));

        return $decoded['uuid'];
    }
}

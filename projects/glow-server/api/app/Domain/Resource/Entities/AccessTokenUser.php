<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

class AccessTokenUser
{
    public function __construct(
        private string $usrUserId,
        private string $deviceId,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }
}

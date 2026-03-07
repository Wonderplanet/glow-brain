<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrDeviceEntity
{
    public function __construct(
        private string $usrDeviceId,
        private string $usrUserId,
        private string $uuid,
        private ?string $bnidLinkedAt,
        private string $osPlatform,
    ) {
    }

    public function getUsrDeviceId(): string
    {
        return $this->usrDeviceId;
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getBnidLinkedAt(): ?string
    {
        return $this->bnidLinkedAt;
    }

    public function getOsPlatform(): string
    {
        return $this->osPlatform;
    }
}

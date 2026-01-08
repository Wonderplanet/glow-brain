<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrUserProfileEntity
{
    public function __construct(
        private string $usrUserId,
        private string $name,
        private string $mstUnitId,
        private string $mstEmblemId,
        private ?int $birthDate,
        private bool $hasBirthDate,
        private string $myId = '',
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getMstEmblemId(): string
    {
        return $this->mstEmblemId;
    }

    public function getBirthDate(): ?int
    {
        return $this->birthDate;
    }

    public function hasBirthDate(): bool
    {
        return $this->hasBirthDate;
    }

    public function getMyId(): string
    {
        return $this->myId;
    }
}

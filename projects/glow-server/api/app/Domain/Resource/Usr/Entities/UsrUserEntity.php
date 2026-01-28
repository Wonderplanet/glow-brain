<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrUserEntity
{
    public function __construct(
        private string $usrUserId,
        private ?string $bnUserId,
        private bool $hasBnUserId,
        private string $tutorialStatus,
        private ?string $gameStartAt,
        private ?string $clientUuid = null,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getBnUserId(): ?string
    {
        return $this->bnUserId;
    }

    public function hasBnUserId(): bool
    {
        return $this->hasBnUserId;
    }

    public function getTutorialStatus(): string
    {
        return $this->tutorialStatus;
    }

    public function getClientUuid(): ?string
    {
        return $this->clientUuid;
    }

    public function getGameStartAt(): ?string
    {
        return $this->gameStartAt;
    }
}

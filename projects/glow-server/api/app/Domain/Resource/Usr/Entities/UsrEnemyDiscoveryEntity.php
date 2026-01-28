<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrEnemyDiscoveryEntity
{
    public function __construct(
        private string $usrUserId,
        private string $mstEnemyCharacterId,
        private int $isNewEncyclopedia,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getMstEnemyCharacterId(): string
    {
        return $this->mstEnemyCharacterId;
    }

    public function getIsNewEncyclopedia(): int
    {
        return $this->isNewEncyclopedia;
    }
}

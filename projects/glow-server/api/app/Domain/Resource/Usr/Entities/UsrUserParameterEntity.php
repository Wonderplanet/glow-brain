<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrUserParameterEntity
{
    public function __construct(
        private string $usrUserId,
        private int $level,
        private int $exp,
        private int $coin,
        private int $stamina,
        private string $staminaUpdatedAt,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getCoin(): int
    {
        return $this->coin;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }

    public function getStaminaUpdatedAt(): string
    {
        return $this->staminaUpdatedAt;
    }
}

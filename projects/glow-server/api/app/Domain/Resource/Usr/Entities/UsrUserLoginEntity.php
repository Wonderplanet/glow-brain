<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrUserLoginEntity
{
    public function __construct(
        private string $usrUserId,
        private ?string $firstLoginAt,
        private int $loginDayCount,
        private int $loginContinueDayCount,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getFirstLoginAt(): ?string
    {
        return $this->firstLoginAt;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }

    public function getLoginContinueDayCount(): int
    {
        return $this->loginContinueDayCount;
    }
}

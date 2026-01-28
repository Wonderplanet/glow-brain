<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrConditionPackEntity
{
    public function __construct(
        private string $usrUserId,
        private string $mstPackId,
        private string $startDate,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getMstPackId(): string
    {
        return $this->mstPackId;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrItemEntity
{
    public function __construct(
        private string $usrUserId,
        private string $mstItemId,
        private int $amount,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getMstItemId(): string
    {
        return $this->mstItemId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}

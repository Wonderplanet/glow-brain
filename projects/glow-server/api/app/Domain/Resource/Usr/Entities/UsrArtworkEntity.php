<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrArtworkEntity
{
    public function __construct(
        private string $usrUserId,
        private string $mstArtworkId
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getMstArtworkId(): string
    {
        return $this->mstArtworkId;
    }
}

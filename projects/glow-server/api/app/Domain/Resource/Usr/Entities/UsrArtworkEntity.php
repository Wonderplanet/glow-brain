<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrArtworkEntity
{
    public function __construct(
        private string $usrUserId,
        private string $mstArtworkId,
        private int $gradeLevel,
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

    public function getGradeLevel(): int
    {
        return $this->gradeLevel;
    }
}

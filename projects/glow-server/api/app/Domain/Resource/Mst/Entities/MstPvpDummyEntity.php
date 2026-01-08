<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPvpDummyEntity
{
    public function __construct(
        private string $id,
        private string $mstDummyUserId,
        private string $rankClassType,
        private int $rankClassLevel,
        private string $matchingType,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstDummyUserId(): string
    {
        return $this->mstDummyUserId;
    }

    public function getRankClassType(): string
    {
        return $this->rankClassType;
    }

    public function getRankClassLevel(): int
    {
        return $this->rankClassLevel;
    }

    public function getMatchingType(): string
    {
        return $this->matchingType;
    }
}

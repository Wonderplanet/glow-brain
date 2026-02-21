<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Pvp\Enums\PvpMatchingType;

class MstPvpMatchingScoreRangeEntity
{
    public function __construct(
        private string $id,
        private string $rankClassType,
        private int $rankClassLevel,
        private int $upperRankMaxScore,
        private int $upperRankMinScore,
        private int $sameRankMaxScore,
        private int $sameRankMinScore,
        private int $lowerRankMaxScore,
        private int $lowerRankMinScore,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRankClassType(): string
    {
        return $this->rankClassType;
    }

    public function getRankClassLevel(): int
    {
        return $this->rankClassLevel;
    }

    public function getUpperRankMaxScore(): int
    {
        return $this->upperRankMaxScore;
    }

    public function getUpperRankMinScore(): int
    {
        return $this->upperRankMinScore;
    }

    public function getSameRankMaxScore(): int
    {
        return $this->sameRankMaxScore;
    }

    public function getSameRankMinScore(): int
    {
        return $this->sameRankMinScore;
    }

    public function getLowerRankMaxScore(): int
    {
        return $this->lowerRankMaxScore;
    }

    public function getLowerRankMinScore(): int
    {
        return $this->lowerRankMinScore;
    }

    public function getMinScoreByType(PvpMatchingType $pvpMatchingType): int
    {
        return match ($pvpMatchingType) {
            PvpMatchingType::Upper => $this->upperRankMinScore,
            PvpMatchingType::Same => $this->sameRankMinScore,
            PvpMatchingType::Lower => $this->lowerRankMinScore,
            default => 0,
        };
    }

    public function getMaxScoreByType(PvpMatchingType $pvpMatchingType): int
    {
        return match ($pvpMatchingType) {
            PvpMatchingType::Upper => $this->upperRankMaxScore,
            PvpMatchingType::Same => $this->sameRankMaxScore,
            PvpMatchingType::Lower => $this->lowerRankMaxScore,
            default => 0,
        };
    }
}

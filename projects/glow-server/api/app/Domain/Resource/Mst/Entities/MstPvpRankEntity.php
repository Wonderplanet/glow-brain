<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Pvp\Enums\PvpRankClassType;

class MstPvpRankEntity
{
    public function __construct(
        private string $id,
        private PvpRankClassType $rankClassType,
        private int $rankClassLevel,
        private int $requiredLowerScore,
        private int $winAddPoint,
        private int $loseSubPoint,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRankClassType(): PvpRankClassType
    {
        return $this->rankClassType;
    }

    public function getRankClassLevel(): int
    {
        return $this->rankClassLevel;
    }

    public function getRequiredLowerScore(): int
    {
        return $this->requiredLowerScore;
    }

    public function getWinAddPoint(): int
    {
        return $this->winAddPoint;
    }

    public function getLoseSubPoint(): int
    {
        return $this->loseSubPoint;
    }
}

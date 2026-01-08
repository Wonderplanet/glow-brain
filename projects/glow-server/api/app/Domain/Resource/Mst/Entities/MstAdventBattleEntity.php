<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\AdventBattle\Enums\AdventBattleType;

class MstAdventBattleEntity
{
    public function __construct(
        private string $id,
        private string $adventBattleType,
        private string $eventBonusGroupId,
        private int $challengeableCount,
        private int $adChallengeableCount,
        private int $exp,
        private int $coin,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAdventBattleType(): string
    {
        return $this->adventBattleType;
    }

    public function isRaid(): bool
    {
        return $this->adventBattleType === AdventBattleType::RAID->value;
    }

    public function getEventBonusGroupId(): string
    {
        return $this->eventBonusGroupId;
    }

    public function getChallengeableCount(): int
    {
        return $this->challengeableCount;
    }

    public function getAdChallengeableCount(): int
    {
        return $this->adChallengeableCount;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getCoin(): int
    {
        return $this->coin;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }
}

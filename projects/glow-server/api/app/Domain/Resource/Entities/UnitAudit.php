<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;

class UnitAudit
{
    public function __construct(
        private readonly MstUnitEntity $mstUnit,
        private readonly CheatCheckUnit $cheatCheckUnit,
        private readonly int $baseHp,
        private readonly int $baseAtk,
        private float $unitEncyclopediaEffectBonusHpPercentage = 0,
        private float $unitEncyclopediaEffectBonusAtkPercentage = 0,
        private int $eventBonusPercentage = 0,
    ) {
    }

    public function getMstUnit(): MstUnitEntity
    {
        return $this->mstUnit;
    }

    public function getCheatCheckUnit(): CheatCheckUnit
    {
        return $this->cheatCheckUnit;
    }

    public function getBaseHp(): int
    {
        return $this->baseHp;
    }

    public function getBaseAtk(): int
    {
        return $this->baseAtk;
    }

    public function getUnitEncyclopediaEffectBonusHpPercentage(): float
    {
        return $this->unitEncyclopediaEffectBonusHpPercentage;
    }

    public function setUnitEncyclopediaEffectBonusHpPercentage(float $unitEncyclopediaEffectBonusHpPercentage): void
    {
        $this->unitEncyclopediaEffectBonusHpPercentage = $unitEncyclopediaEffectBonusHpPercentage;
    }

    public function getUnitEncyclopediaEffectBonusAtkPercentage(): float
    {
        return $this->unitEncyclopediaEffectBonusAtkPercentage;
    }

    public function setUnitEncyclopediaEffectBonusAtkPercentage(float $unitEncyclopediaEffectBonusAtkPercentage): void
    {
        $this->unitEncyclopediaEffectBonusAtkPercentage = $unitEncyclopediaEffectBonusAtkPercentage;
    }

    public function getEventBonusPercentage(): int
    {
        return $this->eventBonusPercentage;
    }

    public function setEventBonusPercentage(int $eventBonusPercentage): void
    {
        $this->eventBonusPercentage = $eventBonusPercentage;
    }

    public function getBoostedHp(): int
    {
        // 計算順序はクライアント側を参考にする
        $effectedHp = $this->calcParam($this->baseHp, $this->unitEncyclopediaEffectBonusHpPercentage);
        return (int) ceil($this->calcParam($effectedHp, $this->eventBonusPercentage));
    }

    public function getBoostedAtk(): int
    {
        // 計算順序はクライアント側を参考にする
        $effectedAtk = $this->calcParam($this->baseAtk, $this->unitEncyclopediaEffectBonusAtkPercentage);
        return (int) ceil($this->calcParam($effectedAtk, $this->eventBonusPercentage));
    }

    private function calcParam(float $baseParam, float $effectPercentage): float
    {
        return ($baseParam * ($effectPercentage + 100) / 100);
    }

    public function isAbility1Unlocked(): bool
    {
        $mstUnit = $this->mstUnit;
        return StringUtil::isSpecified($mstUnit->getMstUnitAbility1()) &&
            $this->cheatCheckUnit->getRank() >= $mstUnit->getAbilityUnlockRank1();
    }

    public function isAbility2Unlocked(): bool
    {
        $mstUnit = $this->mstUnit;
        return StringUtil::isSpecified($mstUnit->getMstUnitAbility2()) &&
            $this->cheatCheckUnit->getRank() >= $mstUnit->getAbilityUnlockRank2();
    }

    public function isAbility3Unlocked(): bool
    {
        $mstUnit = $this->mstUnit;
        return StringUtil::isSpecified($mstUnit->getMstUnitAbility3()) &&
            $this->cheatCheckUnit->getRank() >= $mstUnit->getAbilityUnlockRank3();
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return array_merge(
            $this->cheatCheckUnit->formatToLog(),
            [
                'base_hp' => $this->baseHp,
                'base_atk' => $this->baseAtk,
                'bonus_hp' => $this->getBoostedHp(),
                'bonus_atk' => $this->getBoostedAtk(),
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class UsrParameterData
{
    public function __construct(
        // UsrUserParameter
        private int $level,
        private int $exp,
        private int $coin,
        private int $stamina,
        private ?string $staminaUpdatedAt,
        // UsrCurrencySummaryEntity
        private int $freeDiamond,
        private int $paidDiamondIos,
        private int $paidDiamondAndroid,
    ) {
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getCoin(): int
    {
        return $this->coin;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }

    public function getStaminaUpdatedAt(): ?string
    {
        return $this->staminaUpdatedAt;
    }

    public function getFreeDiamond(): int
    {
        return $this->freeDiamond;
    }

    public function getPaidDiamondIos(): int
    {
        return $this->paidDiamondIos;
    }

    public function getPaidDiamondAndroid(): int
    {
        return $this->paidDiamondAndroid;
    }
}

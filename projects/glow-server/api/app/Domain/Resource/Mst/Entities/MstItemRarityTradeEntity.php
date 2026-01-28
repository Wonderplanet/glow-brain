<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Item\Enums\ItemTradeResetType;

class MstItemRarityTradeEntity
{
    public function __construct(
        private string $id,
        private string $rarity,
        private int $costAmount,
        private string $resetType,
        private ?int $maxTradableAmount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getCostAmount(): int
    {
        return $this->costAmount;
    }

    public function getResetType(): string
    {
        return $this->resetType;
    }

    public function getMaxTradableAmount(): ?int
    {
        return $this->maxTradableAmount;
    }

    public function hasLimitTradeAmount(): bool
    {
        return $this->maxTradableAmount !== null;
    }

    public function getResetTypeEnum(): ?ItemTradeResetType
    {
        return ItemTradeResetType::tryFrom($this->resetType);
    }

    /**
     * リセット期間が設定されているかどうか
     * true: リセットあり, false: リセットなし
     * @return bool
     */
    public function hasResetType(): bool
    {
        $enum = $this->getResetTypeEnum();
        return $enum !== null && $enum !== ItemTradeResetType::NONE;
    }
}

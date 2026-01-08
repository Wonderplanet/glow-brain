<?php

declare(strict_types=1);

namespace App\Domain\Item\Entities;

use App\Domain\Resource\Entities\Rewards\BaseReward;

/**
 * 放置ボックスアイテムをリアルリソースへ交換するためのEntity
 *
 * 基本的に、itemドメインで実体のオブジェクトを生成し、
 * idleIncentiveドメインでItemIdleBoxRewardExchangeInterfaceを介して利用する。
 * interfaceを介さずに直接利用すると、itemとidleIncentiveドメイン間での疎結合性が低下するため。
 */
class ItemIdleBoxRewardExchange implements ItemIdleBoxRewardExchangeInterface
{
    private BaseReward $reward;

    private string $itemType;

    private int $afterAmount;

    private int $idleMinutes;

    public function __construct(
        BaseReward $reward,
        string $itemType,
        int $idleMinutes
    ) {
        $this->reward = $reward;
        $this->itemType = $itemType;
        $this->idleMinutes = $idleMinutes;

        // 元の報酬量をデフォルトとする
        $this->afterAmount = $reward->getAmount();
    }

    public function getRewardId(): string
    {
        return $this->reward->getId();
    }

    public function getType(): string
    {
        return $this->reward->getType();
    }

    public function getResourceId(): ?string
    {
        return $this->reward->getResourceId();
    }

    public function getBeforeAmount(): int
    {
        return $this->reward->getAmount();
    }

    public function getAfterAmount(): int
    {
        return $this->afterAmount;
    }

    public function setAfterAmount(int $afterAmount): void
    {
        $this->afterAmount = $afterAmount;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function getIdleMinutes(): int
    {
        return $this->idleMinutes;
    }
}

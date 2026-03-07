<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $mst_shop_item_id
 * @property int $trade_count
 * @property string $cost_type
 * @property int $cost_amount
 * @property string $received_reward
 */
class LogTradeShopItem extends LogModel
{
    public function setMstShopItemId(string $mstShopItemId): void
    {
        $this->mst_shop_item_id = $mstShopItemId;
    }

    public function setTradeCount(int $tradeCount): void
    {
        $this->trade_count = $tradeCount;
    }

    public function setCostType(string $costType): void
    {
        $this->cost_type = $costType;
    }

    public function setCostAmount(int $costAmount): void
    {
        $this->cost_amount = $costAmount;
    }

    /**
     * @param array<mixed> $receivedReward
     */
    public function setReceivedReward(array $receivedReward): void
    {
        $this->received_reward = json_encode($receivedReward);
    }
}

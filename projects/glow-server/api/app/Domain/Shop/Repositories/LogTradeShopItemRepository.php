<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Shop\Models\LogTradeShopItem;
use Illuminate\Support\Collection;

class LogTradeShopItemRepository extends LogModelRepository
{
    protected string $modelClass = LogTradeShopItem::class;

    /**
     * @param Collection<BaseReward> $receivedReward
     */
    public function create(
        string $usrUserId,
        string $mstShopItemId,
        int $tradeCount,
        string $costType,
        ?int $costAmount,
        Collection $receivedReward
    ): LogTradeShopItem {
        $model = new LogTradeShopItem();
        $model->setUsrUserId($usrUserId);
        $model->setMstShopItemId($mstShopItemId);
        $model->setTradeCount($tradeCount);
        $model->setCostType($costType);
        $model->setCostAmount($costAmount ?? 0);
        $model->setReceivedReward(
            $receivedReward->map(function (BaseReward $reward) {
                return $reward->formatToLog();
            })->all()
        );

        $this->addModel($model);

        return $model;
    }
}

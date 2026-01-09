<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Repositories;

use App\Domain\Exchange\Models\LogExchangeAction;
use App\Domain\Resource\Entities\Rewards\ExchangeTradeReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Resource\Mst\Entities\MstExchangeCostEntity;
use Illuminate\Support\Collection;

class LogExchangeActionRepository extends LogModelRepository
{
    protected string $modelClass = LogExchangeAction::class;

    /**
     * @param Collection<MstExchangeCostEntity> $mstCosts
     * @param Collection<ExchangeTradeReward> $rewards
     */
    public function create(
        string $usrUserId,
        string $mstExchangeId,
        string $mstExchangeLineupId,
        Collection $mstCosts,
        Collection $rewards,
        int $tradeCount,
    ): LogExchangeAction {
        $model = new LogExchangeAction();
        $model->setUsrUserId($usrUserId);
        $model->setMstExchangeId($mstExchangeId);
        $model->setMstExchangeLineupId($mstExchangeLineupId);
        $model->setCosts($mstCosts->map(fn(MstExchangeCostEntity $mstCost) =>
            $mstCost->formatToLog($tradeCount))->toArray());
        $model->setRewards($rewards->map(fn(ExchangeTradeReward $reward) =>
            $reward->formatToLog())->toArray());
        $model->setTradeCount($tradeCount);

        $this->addModel($model);

        return $model;
    }
}

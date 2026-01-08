<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend as IRewardSend;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\User\Models\LogCoin;

class LogCoinRepository extends LogModelRepository implements IRewardSend
{
    protected string $modelClass = LogCoin::class;

    public function create(
        string $usrUserId,
        LogResourceActionType $actionType,
        int $beforeAmount,
        int $afterAmount,
        LogTriggerDto $logTriggerData,
    ): void {
        $model = new LogCoin();
        $model->setUsrUserId($usrUserId);
        $model->setBeforeAmount($beforeAmount);
        $model->setAfterAmount($afterAmount);
        $model->setActionType($actionType);
        $model->setLogTriggerData($logTriggerData);

        $this->addModel($model);
    }

    /**
     * 報酬として獲得したログデータを作成する
     */
    public function createByReward(string $usrUserId, BaseReward $reward): void
    {
        $this->create(
            $usrUserId,
            LogResourceActionType::GET,
            $reward->getBeforeAmount(),
            $reward->getAfterAmount(),
            $reward->getLogTriggerData(),
        );
    }
}

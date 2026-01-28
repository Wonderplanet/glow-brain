<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Repositories;

use App\Domain\Emblem\Models\LogEmblem;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend as IRewardSend;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogEmblemRepository extends LogModelRepository implements IRewardSend
{
    protected string $modelClass = LogEmblem::class;

    public function create(
        string $usrUserId,
        LogResourceActionType $actionType,
        string $mstEmblemId,
        int $amount,
        LogTriggerDto $logTriggerData,
    ): void {
        $model = new LogEmblem();
        $model->setUsrUserId($usrUserId);
        $model->setMstEmblemId($mstEmblemId);
        $model->setAmount($amount);
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
            $reward->getResourceId(),
            $reward->getAmount(),
            $reward->getLogTriggerData(),
        );
    }
}

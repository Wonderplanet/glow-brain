<?php

declare(strict_types=1);

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Models\LogItem;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend as IRewardSend;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogItemRepository extends LogModelRepository implements IRewardSend
{
    protected string $modelClass = LogItem::class;

    public function create(
        string $usrUserId,
        LogResourceActionType $actionType,
        string $mstItemId,
        int $beforeAmount,
        int $afterAmount,
        LogTriggerDto $logTriggerData,
    ): void {
        $model = $this->make(
            $usrUserId,
            $actionType,
            $mstItemId,
            $beforeAmount,
            $afterAmount,
            $logTriggerData,
        );

        $this->addModel($model);
    }

    /**
     * モデルインスタンスを生成するだけのメソッド
     */
    public function make(
        string $usrUserId,
        LogResourceActionType $actionType,
        string $mstItemId,
        int $beforeAmount,
        int $afterAmount,
        LogTriggerDto $logTriggerData,
    ): LogItem {
        $model = new LogItem();
        $model->setUsrUserId($usrUserId);
        $model->setMstItemId($mstItemId);
        $model->setBeforeAmount($beforeAmount);
        $model->setAfterAmount($afterAmount);
        $model->setActionType($actionType);
        $model->setLogTriggerData($logTriggerData);

        return $model;
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
            $reward->getBeforeAmount(),
            $reward->getAfterAmount(),
            $reward->getLogTriggerData(),
        );
    }
}

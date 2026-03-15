<?php

declare(strict_types=1);

namespace App\Domain\Message\Repositories;

use App\Domain\Message\Models\LogSystemMessageAddition;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogSystemMessageAdditionRepository extends LogModelRepository
{
    protected string $modelClass = LogSystemMessageAddition::class;

    public function create(
        string $usrUserId,
        BaseReward $reward,
    ): LogSystemMessageAddition {
        $model = new LogSystemMessageAddition();

        $model->setUsrUserId($usrUserId);

        $logTriggerDto = $reward->getLogTriggerData();
        $model->setTriggerSource($logTriggerDto->getTriggerSource());
        $model->setTriggerValue($logTriggerDto->getTriggerValue());

        $model->setPreGrantRewardJson($reward->formatToResponse());

        $this->addModel($model);

        return $model;
    }
}

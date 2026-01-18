<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\LogMissionReward;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Illuminate\Support\Collection;

class LogMissionRewardRepository extends LogModelRepository
{
    protected string $modelClass = LogMissionReward::class;

    /**
     * @param Collection<BaseReward> $receivedRewards
     */
    public function create(
        string $usrUserId,
        MissionType $missionType,
        Collection $receivedRewards,
    ): LogMissionReward {
        $model = new LogMissionReward();
        $model->setUsrUserId($usrUserId);
        $model->setMissionType($missionType);
        $model->setReceivedReward(
            $receivedRewards->map(
                fn (BaseReward $reward) => $reward->formatToLog()
            )->all()
        );

        $this->addModel($model);

        return $model;
    }
}

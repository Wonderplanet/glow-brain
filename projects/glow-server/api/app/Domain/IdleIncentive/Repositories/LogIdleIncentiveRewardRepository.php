<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Repositories;

use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\IdleIncentive\Models\LogIdleIncentiveReward;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LogIdleIncentiveRewardRepository extends LogModelRepository
{
    protected string $modelClass = LogIdleIncentiveReward::class;

    /**
     * @param Collection<BaseReward> $receivedRewards
     */
    public function create(
        string $usrUserId,
        IdleIncentiveExecMethod $execMethod,
        CarbonImmutable $idleStartedAt,
        int $elapsedMinutes,
        Collection $receivedRewards,
        CarbonImmutable $receivedRewardAt
    ): LogIdleIncentiveReward {
        $model = new LogIdleIncentiveReward();
        $model->setUsrUserId($usrUserId);
        $model->setExecMethod($execMethod);
        $model->setIdleStartedAt($idleStartedAt);
        $model->setElapsedMinutes($elapsedMinutes);
        $model->setReceivedReward(
            $receivedRewards->map(
                fn (BaseReward $reward) => $reward->formatToLog()
            )->all()
        );
        $model->setReceivedRewardAt($receivedRewardAt);

        $this->addModel($model);

        return $model;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\LogEncyclopediaReward;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Illuminate\Support\Collection;

class LogEncyclopediaRewardRepository extends LogModelRepository
{
    protected string $modelClass = LogEncyclopediaReward::class;

    /**
     * @param Collection<BaseReward> $receivedRewards
     */
    public function create(
        string $usrUserId,
        Collection $receivedRewards,
    ): LogEncyclopediaReward {
        $model = new LogEncyclopediaReward();
        $model->setUsrUserId($usrUserId);
        $model->setReceivedReward(
            $receivedRewards->map(
                fn (BaseReward $reward) => $reward->formatToLog()
            )->all()
        );

        $this->addModel($model);

        return $model;
    }
}

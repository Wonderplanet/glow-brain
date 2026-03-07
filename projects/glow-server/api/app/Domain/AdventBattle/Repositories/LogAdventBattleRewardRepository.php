<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Repositories;

use App\Domain\AdventBattle\Models\LogAdventBattleReward;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Illuminate\Support\Collection;

class LogAdventBattleRewardRepository extends LogModelRepository
{
    protected string $modelClass = LogAdventBattleReward::class;

    /**
     * @param Collection<BaseReward> $receivedRewards
     */
    public function create(
        string $usrUserId,
        Collection $receivedRewards,
    ): LogAdventBattleReward {
        $model = new LogAdventBattleReward();
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

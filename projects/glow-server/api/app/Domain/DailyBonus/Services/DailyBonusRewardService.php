<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Services;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Entities\Rewards\ComebackBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionReward;
use App\Domain\Resource\Mst\Entities\Contracts\MstDailyBonusEntityInterface;
use App\Domain\Resource\Mst\Entities\MstComebackBonusEntity;
use App\Domain\Resource\Mst\Entities\MstDailyBonusRewardEntity;
use App\Domain\Resource\Mst\Repositories\MstDailyBonusRewardRepository;
use Illuminate\Support\Collection;

class DailyBonusRewardService
{
    public function __construct(
        protected MstDailyBonusRewardRepository $mstDailyBonusRewardRepository,
    ) {
    }

    /**
     * デイリーボーナスごとに使用し、付与する報酬情報を取得する
     *
     * @return Collection<MissionReward>
     */
    public function calcRewards(
        Collection $mstDailyBonuses,
        Collection $usrDailyBonusProgresses,
    ): Collection {
        $receivableMstDailyBonuses = collect();
        $mstRewardGroupIds = collect();
        $mstDailyBonusesGroupByScheduleId = $mstDailyBonuses->groupBy(
            /** @var \App\Domain\Resource\Mst\Entities\Contracts\MstDailyBonusEntityInterface $mstDailyBonus */
            function ($mstDailyBonus): string {
                return $mstDailyBonus->getMstScheduleId();
            }
        );

        /** @var \App\Domain\DailyBonus\Models\UsrDailyBonusProgressInterface $usrDailyBonusProgress */
        foreach ($usrDailyBonusProgresses as $usrDailyBonusProgress) {
            $mstScheduleId = $usrDailyBonusProgress->getMstScheduleId();
            $mstDailyBonuses = $mstDailyBonusesGroupByScheduleId->get($mstScheduleId);
            if ($mstDailyBonuses === null || $mstDailyBonuses->isEmpty()) {
                continue;
            }

            $mstDailyBonusesKeyByLoginDayCount = $mstDailyBonuses->keyBy(
                fn (MstDailyBonusEntityInterface $mstDailyBonus) => $mstDailyBonus->getLoginDayCount()
            );
            $mstDailyBonus = $mstDailyBonusesKeyByLoginDayCount->get($usrDailyBonusProgress->getProgress());
            if ($mstDailyBonus === null) {
                continue;
            }
            $receivableMstDailyBonuses->push($mstDailyBonus);
            $mstRewardGroupId = $mstDailyBonus->getMstDailyBonusRewardGroupId();
            if (StringUtil::isNotSpecified($mstRewardGroupId)) {
                continue;
            }
            $mstRewardGroupIds->push($mstRewardGroupId);
        }

        /** @var Collection<string, Collection<MstDailyBonusRewardEntity>> */
        $groupedMstRewards = $this->mstDailyBonusRewardRepository
            ->getByGroupIds($mstRewardGroupIds)
            ->groupBy(function (MstDailyBonusRewardEntity $mstReward) {
                return $mstReward->getGroupId();
            });

        $rewards = collect();
        foreach ($receivableMstDailyBonuses as $mstDailyBonus) {
            $mstRewards = $groupedMstRewards->get(
                $mstDailyBonus->getMstDailyBonusRewardGroupId(),
                collect()
            );
            if ($mstRewards->isEmpty()) {
                continue;
            }
            foreach ($mstRewards as $mstReward) {
                $reward = $this->makeReward(
                    $mstDailyBonus,
                    $mstReward,
                );
                $rewards->push($reward);
            }
        }

        return $rewards;
    }

    private function makeReward(
        MstDailyBonusEntityInterface $mstDailyBonusEntity,
        MstDailyBonusRewardEntity $mstDailyBonusRewardEntity,
    ): ComebackBonusReward {
        if ($mstDailyBonusEntity instanceof MstComebackBonusEntity) {
            $reward = $this->makeComebackBonusReward(
                $mstDailyBonusEntity,
                $mstDailyBonusRewardEntity,
            );
        } else {
            // TODO: 現状カムバックボーナスしか無い為、ミッションからデイリーボーナスを移植する際に差し替える
            // TODO: sail checkに引っかかるので解消する
            $reward = $this->makeComebackBonusReward(
                $mstDailyBonusEntity,
                $mstDailyBonusRewardEntity,
            );
        }

        return $reward;
    }

    private function makeComebackBonusReward(
        MstDailyBonusEntityInterface $mstComebackBonusEntity,
        MstDailyBonusRewardEntity $mstDailyBonusRewardEntity,
    ): ComebackBonusReward {
        return new ComebackBonusReward(
            $mstDailyBonusRewardEntity->getResourceType(),
            $mstDailyBonusRewardEntity->getResourceId(),
            $mstDailyBonusRewardEntity->getResourceAmount(),
            $mstComebackBonusEntity->getMstScheduleId(),
            $mstComebackBonusEntity->getId(),
            $mstComebackBonusEntity->getLoginDayCount(),
        );
    }
}

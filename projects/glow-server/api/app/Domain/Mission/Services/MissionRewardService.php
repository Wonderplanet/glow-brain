<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Entities\Rewards\MissionDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionReward;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityReceiveRewardInterface;
use App\Domain\Resource\Mst\Entities\MstMissionDailyBonusEntity;
use App\Domain\Resource\Mst\Entities\MstMissionEventDailyBonusEntity;
use App\Domain\Resource\Mst\Entities\MstMissionRewardEntity;
use App\Domain\Resource\Mst\Repositories\MstMissionRewardRepository;
use Illuminate\Support\Collection;

class MissionRewardService
{
    public function __construct(
        protected MstMissionRewardRepository $mstMissionRewardRepository,
    ) {
    }

    /**
     * ミッションタイプごとに使用し、付与する報酬情報を取得する
     *
     * @return Collection<MissionReward>
     */
    public function calcRewards(
        MissionType $missionType,
        Collection $mstMissions,
        Collection $usrMissions,
    ): Collection {
        $receivableMstMissionIds = collect();
        $mstRewardGroupIds = collect();
        foreach ($usrMissions as $usrMission) {
            /** @var \App\Domain\Mission\Models\UsrMissionInterface $usrMission */
            if ($usrMission->canReceiveReward() === false) {
                continue;
            }

            $mstMissionId = $usrMission->getMstMissionId();
            /** @var null|MstMissionEntityReceiveRewardInterface $mstMission */
            $mstMission = $mstMissions->get($mstMissionId);
            if ($mstMission === null) {
                continue;
            }
            $receivableMstMissionIds->push($mstMissionId);

            /** @var MstMissionEntityReceiveRewardInterface $mstMission */
            $mstRewardGroupId = $mstMission->getMstMissionRewardGroupId();
            if (StringUtil::isNotSpecified($mstRewardGroupId)) {
                continue;
            }
            $mstRewardGroupIds->push($mstRewardGroupId);
        }

        /** @var Collection<string, Collection<MstMissionRewardEntity>> */
        $groupedMstRewards = $this->mstMissionRewardRepository
            ->getByGroupIds($mstRewardGroupIds)
            ->groupBy(function (MstMissionRewardEntity $mstReward) {
                return $mstReward->getGroupId();
            });

        $rewards = collect();
        foreach ($receivableMstMissionIds as $mstMissionId) {
            $mstMission = $mstMissions->get($mstMissionId);
            if ($mstMission === null) {
                continue;
            }
            $mstRewardGroupId = $mstMission->getMstMissionRewardGroupId();
            if (StringUtil::isNotSpecified($mstRewardGroupId)) {
                continue;
            }

            $mstRewards = $groupedMstRewards->get(
                $mstRewardGroupId,
                collect()
            );
            foreach ($mstRewards as $mstReward) {
                $reward = $this->makeReward(
                    $mstMission,
                    $mstReward,
                );
                $rewards->push($reward);
            }
        }

        return $rewards;
    }

    private function makeReward(
        MstMissionEntityReceiveRewardInterface $mstMission,
        MstMissionRewardEntity $mstReward,
    ): MissionReward|MissionDailyBonusReward|MissionEventDailyBonusReward {
        if ($mstMission instanceof MstMissionDailyBonusEntity) {
            $reward = $this->makeMissionDailyBonusReward(
                $mstMission,
                $mstReward,
            );
        } elseif ($mstMission instanceof MstMissionEventDailyBonusEntity) {
            $reward = $this->makeMissionEventDailyBonusReward(
                $mstMission,
                $mstReward,
            );
        } else {
            $reward = $this->makeMissionReward(
                $mstMission,
                $mstReward,
            );
        }

        return $reward;
    }

    private function makeMissionReward(
        MstMissionEntityReceiveRewardInterface $mstMission,
        MstMissionRewardEntity $mstReward,
    ): MissionReward {
        return new MissionReward(
            $mstReward->getResourceType(),
            $mstReward->getResourceId(),
            $mstReward->getResourceAmount(),
            $mstMission,
        );
    }

    private function makeMissionDailyBonusReward(
        MstMissionDailyBonusEntity $mstMission,
        MstMissionRewardEntity $mstReward,
    ): MissionDailyBonusReward {
        return new MissionDailyBonusReward(
            $mstReward->getResourceType(),
            $mstReward->getResourceId(),
            $mstReward->getResourceAmount(),
            $mstMission,
        );
    }

    private function makeMissionEventDailyBonusReward(
        MstMissionEventDailyBonusEntity $mstMission,
        MstMissionRewardEntity $mstReward,
    ): MissionEventDailyBonusReward {
        return new MissionEventDailyBonusReward(
            $mstReward->getResourceType(),
            $mstReward->getResourceId(),
            $mstReward->getResourceAmount(),
            $mstMission,
        );
    }
}

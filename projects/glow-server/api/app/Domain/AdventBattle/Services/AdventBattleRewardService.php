<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleReceivableReward;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Resource\Entities\Rewards\AdventBattleReward;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardRepository;
use Illuminate\Support\Collection;

abstract class AdventBattleRewardService
{
    public function __construct(
        protected readonly MstAdventBattleRewardRepository $mstAdventBattleRewardRepository,
        protected readonly MstAdventBattleRewardGroupRepository $mstAdventBattleRewardGroupRepository,
        protected readonly AdventBattleRewardCategory $adventBattleRewardCategory,
        protected readonly string $adventBattleRewardClassName = AdventBattleReward::class,
    ) {
    }

    /**
     * 取得可能な報酬情報を取得
     *
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @return AdventBattleReceivableReward
     */
    abstract public function fetchAvailableRewards(
        UsrAdventBattleInterface $usrAdventBattle
    ): AdventBattleReceivableReward;

    /**
     * 報酬グループIDから受け取れる報酬情報を生成
     *
     * @param Collection $mstAdventBattleRewardGroupIds
     * @param string|null $latestMstAdventBattleRewardGroupId
     * @return AdventBattleReceivableReward
     */
    protected function createAdventBattleReceivableReward(
        Collection $mstAdventBattleRewardGroupIds,
        ?string $latestMstAdventBattleRewardGroupId = null,
    ): AdventBattleReceivableReward {
        if ($mstAdventBattleRewardGroupIds->isEmpty()) {
            return new AdventBattleReceivableReward(collect(), $latestMstAdventBattleRewardGroupId);
        }

        return new AdventBattleReceivableReward(
            $this->mstAdventBattleRewardRepository->getByGroupIds($mstAdventBattleRewardGroupIds),
            $latestMstAdventBattleRewardGroupId,
        );
    }

    /**
     * 報酬グループを取得
     *
     * @param string $usrAdventBattleId
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    protected function getRewardGroups(string $usrAdventBattleId): Collection
    {
        return $this->mstAdventBattleRewardGroupRepository->getByAdventBattleIdAndCategory(
            $usrAdventBattleId,
            $this->adventBattleRewardCategory->value,
        );
    }

    /**
     * 付与用の報酬データに変換
     *
     * @param string $mstAdventBattleId
     * @param Collection<\App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity> $mstAdventBattleRewards
     * @return Collection<AdventBattleReward>
     */
    public function convertRewards(string $mstAdventBattleId, Collection $mstAdventBattleRewards): Collection
    {
        $adventBattleRewardCategory = $this->adventBattleRewardCategory->value;
        return $mstAdventBattleRewards->map(function ($entity) use ($mstAdventBattleId, $adventBattleRewardCategory) {
            /** @var \App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity $entity */
            return new $this->adventBattleRewardClassName(
                $entity->getResourceType(),
                $entity->getResourceId(),
                $entity->getResourceAmount() ?? 0,
                $mstAdventBattleId,
                $entity->getMstAdventBattleRewardGroupId(),
                $entity->getId(),
                $adventBattleRewardCategory,
            );
        });
    }

    /**
     * 未受取の報酬グループIDを取得
     *
     * @param string|null $receivedRewardGroupId
     * @param Collection<string> $mstAdventBattleRewardGroupIds
     * @return Collection<string>
     */
    public function getUnreceivedRewardGroupIds(
        ?string $receivedRewardGroupId,
        Collection $mstAdventBattleRewardGroupIds,
    ): Collection {
        // 指定のグループIDまでの要素を削ったコレクションを取得
        $resultRewards = $mstAdventBattleRewardGroupIds
            ->values()
            ->skipUntil(fn($groupId) => $groupId === $receivedRewardGroupId);

        // 指定のグループIDが見つからなかった場合、全て未取得と判断して返す
        if ($resultRewards->isEmpty()) {
            return $mstAdventBattleRewardGroupIds;
        }

        // 指定のグループIDが見つかった場合、それ以降の要素を返す
        return $resultRewards->slice(1)->values();
    }
}

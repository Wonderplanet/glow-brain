<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstAdventBattleClearRewardEntity;
use App\Domain\Resource\Mst\Models\MstAdventBattleClearReward;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstAdventBattleClearRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstAdventBattleClearReward::class);
    }

    /**
     * @param string $mstAdventBattleId
     * @return Collection<MstAdventBattleClearRewardEntity>
     */
    public function getFirstClearRewardsByMstAdventBattleId(string $mstAdventBattleId): Collection
    {
        return $this->getAll()->filter(
            fn(MstAdventBattleClearRewardEntity $entity) =>
            $entity->getMstAdventBattleId() === $mstAdventBattleId
            && $entity->isFirstClear()
        );
    }

    /**
     * @param string $mstAdventBattleId
     * @return Collection<MstAdventBattleClearRewardEntity>
     */
    public function getAlwaysRewardsByMstAdventBattleId(string $mstAdventBattleId): Collection
    {
        return $this->getAll()->filter(
            fn(MstAdventBattleClearRewardEntity $entity) =>
            $entity->getMstAdventBattleId() === $mstAdventBattleId
            && $entity->isAlways()
        );
    }

    /**
     * @param string $mstAdventBattleId
     * @return Collection<MstAdventBattleClearRewardEntity>
     */
    public function getRandomRewardsByMstAdventBattleId(string $mstAdventBattleId): Collection
    {
        return $this->getAll()->filter(
            fn(MstAdventBattleClearRewardEntity $entity) =>
                $entity->getMstAdventBattleId() === $mstAdventBattleId
                && $entity->isRandom()
        );
    }
}

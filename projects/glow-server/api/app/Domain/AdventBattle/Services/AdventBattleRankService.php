<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\Resource\Mst\Entities\MstAdventBattleRankEntity;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRankRepository;
use Illuminate\Support\Collection;

class AdventBattleRankService
{
    public function __construct(
        // Repository
        private readonly MstAdventBattleRankRepository $mstAdventBattleRankRepository,
    ) {
    }

    /**
     * 対象累計スコア以下が条件のランク情報を取得する
     *
     * @param string $mstAdventBattleId
     * @param int    $totalScore
     * @return MstAdventBattleRankEntity|null
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getRankByTotalScore(string $mstAdventBattleId, int $totalScore): ?MstAdventBattleRankEntity
    {
        return $this->mstAdventBattleRankRepository
            ->getByAdventBattleId($mstAdventBattleId)
            ->filter(function ($entity) use ($totalScore) {
                /** @var MstAdventBattleRankEntity $entity */
                return ($entity->getRequiredLowerScore()) <= $totalScore;
            })
            ->sortByDesc(fn($entity) => $entity->getRequiredLowerScore())
            ->first();
    }

    /**
     * 対象累計スコア以下が条件のランク情報を全て取得する
     *
     * @param string $mstAdventBattleId
     * @param int    $totalScore
     * @return Collection<MstAdventBattleRankEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getRanksByTotalScore(string $mstAdventBattleId, int $totalScore): Collection
    {
        return $this->mstAdventBattleRankRepository
            ->getByAdventBattleId($mstAdventBattleId)
            ->filter(function ($entity) use ($totalScore) {
                /** @var MstAdventBattleRankEntity $entity */
                return ($entity->getRequiredLowerScore()) <= $totalScore;
            })
            ->sortBy(fn($entity) => $entity->getRequiredLowerScore())
            ->values();
    }
}

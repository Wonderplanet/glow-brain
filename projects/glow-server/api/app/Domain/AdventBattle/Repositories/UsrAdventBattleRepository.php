<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Repositories;

use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrAdventBattleRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrAdventBattle::class;

    public function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrAdventBattleInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_advent_battle_id' => $model->getMstAdventBattleId(),
                'max_score' => $model->getMaxScore(),
                'total_score' => $model->getTotalScore(),
                'challenge_count' => $model->getChallengeCount(),
                'reset_challenge_count' => $model->getResetChallengeCount(),
                'reset_ad_challenge_count' => $model->getResetAdChallengeCount(),
                'clear_count' => $model->getClearCount(),
                'max_received_max_score_reward' => $model->getMaxReceivedMaxScoreReward(),
                'received_rank_reward_group_id' => $model->getReceivedRankRewardGroupId(),
                'received_raid_reward_group_id' => $model->getReceivedRaidRewardGroupId(),
                'is_ranking_reward_received' => $model->isRankingRewardReceived(),
                'is_excluded_ranking' => $model->isExcludedRanking(),
                'latest_reset_at' => $model->getLatestResetAt(),
                'max_score_party' => $model->getMaxScoreParty(),
            ];
        })->toArray();

        UsrAdventBattle::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_advent_battle_id'],
        );
    }

    public function findByMstAdventBattleId(string $usrUserId, string $mstAdventBattleId): ?UsrAdventBattleInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_advent_battle_id',
            $mstAdventBattleId,
            function () use ($usrUserId, $mstAdventBattleId) {
                return UsrAdventBattle::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_advent_battle_id', $mstAdventBattleId)
                    ->first();
            },
        );
    }

    public function findByMstAdventBattleIds(string $usrUserId, Collection $mstAdventBattleIds): Collection
    {
        if ($mstAdventBattleIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstAdventBattleIds) {
                return $cache->filter(function (UsrAdventBattleInterface $model) use ($mstAdventBattleIds) {
                    return $mstAdventBattleIds->contains($model->getMstAdventBattleId());
                });
            },
            expectedCount: $mstAdventBattleIds->count(),
            dbCallback: function () use ($usrUserId, $mstAdventBattleIds) {
                return UsrAdventBattle::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_advent_battle_id', $mstAdventBattleIds)
                    ->get();
            },
        )->keyBy(function (UsrAdventBattleInterface $model) {
            return $model->getMstAdventBattleId();
        });
    }


    /**
     * @api
     * @param string $usrUserId
     * @return Collection<UsrAdventBattleInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function create(
        string $usrUserId,
        string $mstAdventBattleId,
        ?CarbonImmutable $now = null,
    ): UsrAdventBattleInterface {
        $usrAdventBattle = new UsrAdventBattle();
        $usrAdventBattle->usr_user_id = $usrUserId;
        $usrAdventBattle->mst_advent_battle_id = $mstAdventBattleId;
        $usrAdventBattle->max_score = 0;
        $usrAdventBattle->total_score = 0;
        $usrAdventBattle->challenge_count = 0;
        $usrAdventBattle->reset_challenge_count = 0;
        $usrAdventBattle->reset_ad_challenge_count = 0;
        $usrAdventBattle->clear_count = 0;
        $usrAdventBattle->max_received_max_score_reward = 0;
        $usrAdventBattle->is_ranking_reward_received = false;
        $usrAdventBattle->is_excluded_ranking = false;
        $usrAdventBattle->latest_reset_at = $now?->toDateTimeString();
        $usrAdventBattle->max_score_party = null;
        $this->syncModel($usrAdventBattle);

        return $usrAdventBattle;
    }

    /**
     * 指定したユーザーIDリストに紐づく降臨バトル情報を取得
     *
     * @param Collection<string> $usrUserIds
     * @return Collection<string, int> usr_user_id => total_score
     */
    public function getTotalScoresByUsrUserIds(Collection $usrUserIds, string $mstAdventBattleId): Collection
    {
        if ($usrUserIds->isEmpty()) {
            return collect();
        }

        /** @var Collection<string, int> $result */
        $result = UsrAdventBattle::query()
            ->select([
                'usr_user_id',
                'mst_advent_battle_id',
                'total_score',
            ])
            ->whereIn('usr_user_id', $usrUserIds)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->get()
            ->mapWithKeys(function (UsrAdventBattleInterface $model) {
                return [$model->getUsrUserId() => $model->getTotalScore()];
            });

        return $result;
    }
}

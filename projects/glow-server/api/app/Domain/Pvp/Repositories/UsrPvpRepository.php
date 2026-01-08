<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

// 1ユーザーあたりのレコード数が、2つ以上想定される場合
class UsrPvpRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrPvp::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        /**
         * @return array<string, mixed>
         */
        $upsertValues = $models->map(function (UsrPvpInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'sys_pvp_season_id' => $model->getSysPvpSeasonId(),
                'score' => $model->getScore(),
                'max_received_score_reward' => $model->getMaxReceivedScoreReward(),
                'pvp_rank_class_type' => $model->getPvpRankClassType(),
                'pvp_rank_class_level' => $model->getPvpRankClassLevel(),
                'ranking' => $model->getRanking(),
                'daily_remaining_challenge_count' => $model->getDailyRemainingChallengeCount(),
                'daily_remaining_item_challenge_count' => $model->getDailyRemainingItemChallengeCount(),
                'last_played_at' => $model->getLastPlayedAt(),
                'selected_opponent_candidates' => $model->getSelectedOpponentCandidates(),
                'is_excluded_ranking' => $model->isExcludedRanking(),
                'is_season_reward_received' => $model->isSeasonRewardReceived(),
                'latest_reset_at' => $model->getLatestResetAt(),
            ];
        })->toArray();

        UsrPvp::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'sys_pvp_season_id'],
        );
    }

    public function create(
        string $usrUserId,
        string $sysPvpSeasonId,
        int $maxDailyChallengeCount,
        int $maxDailyItemChallengeCount,
        CarbonImmutable $now,
    ): UsrPvpInterface {
        $model = $this->make(
            $usrUserId,
            $sysPvpSeasonId,
            $maxDailyChallengeCount,
            $maxDailyItemChallengeCount,
            $now,
        );

        $this->syncModel($model);
        return $model;
    }

    private function make(
        string $usrUserId,
        string $sysPvpSeasonId,
        int $maxDailyChallengeCount,
        int $maxDailyItemChallengeCount,
        CarbonImmutable $now,
    ): UsrPvpInterface {
        $model = new UsrPvp([]);
        $model->usr_user_id = $usrUserId;
        $model->sys_pvp_season_id = $sysPvpSeasonId;
        $model->score = 0;
        $model->max_received_score_reward = 0;
        $model->pvp_rank_class_type = PvpRankClassType::BRONZE->value;
        $model->pvp_rank_class_level = 0;
        $model->ranking = 0;
        $model->last_played_at = null;
        $model->selected_opponent_candidates = [];
        $model->is_excluded_ranking = false;
        $model->is_season_reward_received = false;
        $model->daily_remaining_challenge_count = $maxDailyChallengeCount;
        $model->daily_remaining_item_challenge_count = $maxDailyItemChallengeCount;
        $model->latest_reset_at = $now->toDateTimeString();

        return $model;
    }

    public function getOrMake(
        string $usrUserId,
        string $sysPvpSeasonId,
        int $maxDailyChallengeCount,
        int $maxDailyItemChallengeCount,
        CarbonImmutable $now,
    ): UsrPvpInterface {
        $model = $this->cachedGetOneWhere(
            $usrUserId,
            'sys_pvp_season_id',
            $sysPvpSeasonId,
            function () use ($usrUserId, $sysPvpSeasonId) {
                return UsrPvp::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('sys_pvp_season_id', $sysPvpSeasonId)
                    ->first();
            },
        );
        if ($model === null) {
            $model = $this->make(
                $usrUserId,
                $sysPvpSeasonId,
                $maxDailyChallengeCount,
                $maxDailyItemChallengeCount,
                $now,
            );
        }
        return $model;
    }

    public function getBySysPvpSeasonId(
        string $usrUserId,
        string $sysPvpSeasonId,
        bool $isThrowError = false
    ): ?UsrPvpInterface {
        $model = $this->cachedGetOneWhere(
            $usrUserId,
            'sys_pvp_season_id',
            $sysPvpSeasonId,
            function () use ($usrUserId, $sysPvpSeasonId) {
                return UsrPvp::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('sys_pvp_season_id', $sysPvpSeasonId)
                    ->first();
            },
        );

        if ($model === null && $isThrowError) {
            throw new GameException(
                ErrorCode::PVP_SESSION_NOT_FOUND,
                "User PVP information not found for user: {$usrUserId}, season: {$sysPvpSeasonId}"
            );
        }
        return $model;
    }

    public function getBySysPvpSeasonIds(
        string $usrUserId,
        Collection $sysPvpSeasonIds,
    ): Collection {
        $sysPvpSeasonIds = $sysPvpSeasonIds->filter();
        if ($sysPvpSeasonIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($sysPvpSeasonIds) {
                return $cache->filter(function (UsrPvpInterface $model) use ($sysPvpSeasonIds) {
                    return $sysPvpSeasonIds->contains($model->getSysPvpSeasonId());
                });
            },
            expectedCount: count($sysPvpSeasonIds),
            dbCallback: function () use ($usrUserId, $sysPvpSeasonIds) {
                return UsrPvp::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('sys_pvp_season_id', $sysPvpSeasonIds->toArray())
                    ->get();
            },
        );
    }
}

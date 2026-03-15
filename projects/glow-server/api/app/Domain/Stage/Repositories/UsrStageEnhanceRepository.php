<?php

declare(strict_types=1);

namespace App\Domain\Stage\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageEnhanceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

// 1ユーザーあたりのレコード数が、2つ以上想定される場合
class UsrStageEnhanceRepository extends UsrModelMultiCacheRepository implements IUsrStageRepository
{
    protected string $modelClass = UsrStageEnhance::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrStageEnhance $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_stage_id' => $model->getMstStageId(),
                'clear_count' => $model->getClearCount(),
                'reset_challenge_count' => $model->getResetChallengeCount(),
                'reset_ad_challenge_count' => $model->getResetAdChallengeCount(),
                'max_score' => $model->getMaxScore(),
                'latest_reset_at' => $model->getLatestResetAt(),
            ];
        })->toArray();

        UsrStageEnhance::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_stage_id'],
            ['clear_count', 'reset_challenge_count', 'reset_ad_challenge_count', 'max_score', 'latest_reset_at'],
        );
    }

    public function findByMstStageId(string $usrUserId, string $mstStageId): ?UsrStageEnhanceInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_stage_id',
            $mstStageId,
            function () use ($usrUserId, $mstStageId) {
                return UsrStageEnhance::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_stage_id', $mstStageId)
                    ->first();
            },
        );
    }

    public function findByMstStageIds(string $usrUserId, Collection $mstStageIds): Collection
    {
        if ($mstStageIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstStageIds) {
                return $cache->filter(function (UsrStageEnhanceInterface $model) use ($mstStageIds) {
                    return $mstStageIds->contains($model->getMstStageId());
                });
            },
            expectedCount: $mstStageIds->count(),
            dbCallback: function () use ($usrUserId, $mstStageIds) {
                return UsrStageEnhance::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_stage_id', $mstStageIds)
                    ->get();
            },
        )->keyBy(function (UsrStageEnhanceInterface $model) {
            return $model->getMstStageId();
        });
    }

    public function create(
        string $usrUserId,
        string $mstStageId,
        ?CarbonImmutable $now = null,
    ): UsrStageEnhanceInterface {
        $usrStageEnhance = new UsrStageEnhance();
        $usrStageEnhance->usr_user_id = $usrUserId;
        $usrStageEnhance->mst_stage_id = $mstStageId;
        $usrStageEnhance->clear_count = 0;
        $usrStageEnhance->reset_challenge_count = 0;
        $usrStageEnhance->reset_ad_challenge_count = 0;
        $usrStageEnhance->max_score = 0;
        $usrStageEnhance->latest_reset_at = $now?->toDateTimeString();

        $this->syncModel($usrStageEnhance);

        return $usrStageEnhance;
    }

    /**
     * @return Collection<UsrStageEnhanceInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }
}

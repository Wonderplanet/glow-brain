<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm as EloquentUsrMissionLimitedTerm;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\UsrMissionLimitedTermInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMissionLimitedTermRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrMissionLimitedTerm::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrMissionLimitedTerm $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_mission_limited_term_id' => $model->getMstMissionId(),
                'status' => $model->getStatus(),
                'is_open' => $model->getIsOpen(),
                'progress' => $model->getProgress(),
                'latest_reset_at' => $model->getLatestResetAt(),
                'cleared_at' => $model->getClearedAt(),
                'received_reward_at' => $model->getReceivedRewardAt(),
            ];
        })->toArray();

        EloquentUsrMissionLimitedTerm::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_mission_limited_term_id'],
            [
                'status',
                'is_open',
                'progress',
                'latest_reset_at',
                'cleared_at',
                'received_reward_at',
            ],
        );
    }

    public function create(
        string $usrUserId,
        string $mstMissionId,
        CarbonImmutable $now,
    ): UsrMissionLimitedTermInterface|IUsrMission {
        $model = UsrMissionLimitedTerm::create(
            usrUserId: $usrUserId,
            mstMissionLimitedTermId: $mstMissionId,
            now: $now,
        );

        $this->syncModel($model);

        return $model;
    }

    /**
     * @param string $usrUserId
     * @param Collection<string> $mstMissionIds
     * @return Collection<string, UsrMissionLimitedTermInterface|IUsrMission>
     */
    public function getByMstMissionIds(string $usrUserId, Collection $mstMissionIds): Collection
    {
        if ($mstMissionIds->isEmpty()) {
            return collect();
        }
        $targetMstMissionIds = array_fill_keys($mstMissionIds->all(), true);

        $model = $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetMstMissionIds) {
                return $cache->filter(function (UsrMissionLimitedTermInterface $model) use ($targetMstMissionIds) {
                    return isset($targetMstMissionIds[$model->getMstMissionId()]);
                });
            },
            expectedCount: count($targetMstMissionIds),
            dbCallback: function () use ($usrUserId, $targetMstMissionIds) {
                return UsrMissionLimitedTerm::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_mission_limited_term_id', array_keys($targetMstMissionIds))
                    ->get()
                    ->map(function ($record) {
                        return UsrMissionLimitedTerm::createFromRecord($record);
                    });
            },
        );

        return $model->keyBy(fn (UsrMissionLimitedTermInterface $model) => $model->getMstMissionId());
    }

    /**
     * 報酬受け取り可能なデータを全取得する
     *
     * 期待するデータ数は未知のため、expectedCountにnullを指定する。
     *
     * DB保存実行はUseCaseの最後なので、それまでに変更があったモデルはこのメソッド実行時にはまだDBに反映されていない。
     * そのため、DBに反映されていないが変更があるモデルと、UsrModelManagerに格納されていないモデルの両方を取得する必要がある。
     */
    public function getReceivableRewards(
        string $usrUserId,
    ): Collection {
        $models = $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) {
                return $cache->filter(function (UsrMissionLimitedTermInterface $model) {
                    return $model->getIsOpen() === MissionUnlockStatus::OPEN->value
                        && $model->getStatus() === MissionStatus::CLEAR->value;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId) {
                return UsrMissionLimitedTerm::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('status', MissionStatus::CLEAR->value)
                    ->where('is_open', MissionUnlockStatus::OPEN->value)
                    ->get()
                    ->map(function ($record) {
                        return UsrMissionLimitedTerm::createFromRecord($record);
                    });
            },
        );

        return $models;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Models\UsrMissionDailyBonusInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrMissionDailyBonusRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrMissionDailyBonus::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrMissionDailyBonus $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_mission_daily_bonus_id' => $model->getMstMissionDailyBonusId(),
                'status' => $model->getStatus(),
                'cleared_at' => $model->getClearedAt(),
                'received_reward_at' => $model->getReceivedRewardAt(),
            ];
        })->toArray();

        UsrMissionDailyBonus::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_mission_daily_bonus_id'],
            ['status', 'cleared_at', 'received_reward_at'],
        );
    }

    public function create(string $usrUserId, string $mstMissionId): UsrMissionDailyBonusInterface
    {
        $model = new UsrMissionDailyBonus();

        $model->usr_user_id = $usrUserId;
        $model->mst_mission_daily_bonus_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR->value;
        $model->cleared_at = null;
        $model->received_reward_at = null;

        $this->syncModel($model);

        return $model;
    }

    public function getByMstMissionId(string $usrUserId, string $mstMissionId): ?UsrMissionDailyBonusInterface
    {
        return $this->cachedGetOneWhere(
            usrUserId: $usrUserId,
            columnKey: 'mst_mission_daily_bonus_id',
            columnValue: $mstMissionId,
            dbCallback: function () use ($usrUserId, $mstMissionId) {
                return UsrMissionDailyBonus::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_mission_daily_bonus_id', $mstMissionId)
                    ->first();
            },
        );
    }

    /**
     * @return Collection<UsrMissionDailyBonusInterface>
     */
    public function getByMstMissionIds(string $usrUserId, Collection $mstMissionIds): Collection
    {
        if ($mstMissionIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstMissionIds) {
                return $cache->filter(function (UsrMissionDailyBonusInterface $model) use ($mstMissionIds) {
                    return $mstMissionIds->contains($model->getMstMissionId());
                });
            },
            expectedCount: $mstMissionIds->count(),
            dbCallback: function () use ($usrUserId, $mstMissionIds) {
                return UsrMissionDailyBonus::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_mission_daily_bonus_id', $mstMissionIds->toArray())
                    ->get();
            },
        );
    }
}

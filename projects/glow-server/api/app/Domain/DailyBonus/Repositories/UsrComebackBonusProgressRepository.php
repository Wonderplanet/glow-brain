<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Repositories;

use App\Domain\DailyBonus\Models\UsrComebackBonusProgress as ComebackBonusProgress;
use App\Domain\DailyBonus\Models\UsrComebackBonusProgressInterface as ComebackBonusProgressInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrComebackBonusProgressRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = ComebackBonusProgress::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (ComebackBonusProgress $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_comeback_bonus_schedule_id' => $model->getMstScheduleId(),
                'start_count' => $model->getStartCount(),
                'progress' => $model->getProgress(),
                'latest_update_at' => $model->getLatestUpdateAt(),
                'start_at' => $model->getStartAt(),
                'end_at' => $model->getEndAt(),
            ];
        })->toArray();

        ComebackBonusProgress::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_comeback_bonus_schedule_id'],
            ['start_count', 'progress', 'latest_update_at', 'start_at', 'end_at'],
        );
    }

    public function create(
        string $usrUserId,
        string $mstComebackBonusScheduleId,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
    ): ComebackBonusProgressInterface {
        $model = new ComebackBonusProgress();

        $model->usr_user_id = $usrUserId;
        $model->mst_comeback_bonus_schedule_id = $mstComebackBonusScheduleId;
        $model->start_count = 1;
        $model->progress = 0;
        $model->latest_update_at = null;
        $model->start_at = $startAt->toDateTimeString();
        $model->end_at = $endAt->toDateTimeString();

        $this->syncModel($model);

        return $model;
    }

    public function getByMstScheduleId(string $usrUserId, string $mstScheduleId): ?ComebackBonusProgressInterface
    {
        return $this->cachedGetOneWhere(
            usrUserId: $usrUserId,
            columnKey: 'mst_comeback_bonus_schedule_id',
            columnValue: $mstScheduleId,
            dbCallback: function () use ($usrUserId, $mstScheduleId) {
                return ComebackBonusProgress::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_comeback_bonus_schedule_id', $mstScheduleId)
                    ->first();
            },
        );
    }

    /**
     * @return Collection<ComebackBonusProgressInterface>
     */
    public function getByMstScheduleIds(string $usrUserId, Collection $mstScheduleIds): Collection
    {
        if ($mstScheduleIds->isEmpty()) {
            return collect();
        }
        $mstScheduleIds = $mstScheduleIds->unique();
        return $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstScheduleIds) {
                return $cache->filter(function (ComebackBonusProgressInterface $model) use ($mstScheduleIds) {
                    return $mstScheduleIds->contains($model->getMstScheduleId());
                });
            },
            expectedCount: $mstScheduleIds->count(),
            dbCallback: function () use ($usrUserId, $mstScheduleIds) {
                return ComebackBonusProgress::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_comeback_bonus_schedule_id', $mstScheduleIds->toArray())
                    ->get();
            },
        );
    }
}

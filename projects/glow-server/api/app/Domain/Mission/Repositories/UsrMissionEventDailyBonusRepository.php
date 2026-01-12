<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMissionEventDailyBonusRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrMissionEventDailyBonus::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrMissionEventDailyBonus $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_mission_event_daily_bonus_id' => $model->getMstMissionEventDailyBonusId(),
                'status' => $model->getStatus(),
                'cleared_at' => $model->getClearedAt(),
                'received_reward_at' => $model->getReceivedRewardAt(),
            ];
        })->toArray();

        UsrMissionEventDailyBonus::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_mission_event_daily_bonus_id'],
            ['status', 'cleared_at', 'received_reward_at'],
        );
    }

    public function create(string $usrUserId, string $mstMissionId): UsrMissionEventDailyBonusInterface
    {
        $model = new UsrMissionEventDailyBonus();

        $model->usr_user_id = $usrUserId;
        $model->mst_mission_event_daily_bonus_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR->value;
        $model->cleared_at = null;
        $model->received_reward_at = null;

        $this->syncModel($model);

        return $model;
    }

    public function getByMstMissionId(string $usrUserId, string $mstMissionId): ?UsrMissionEventDailyBonusInterface
    {
        return $this->cachedGetOneWhere(
            usrUserId: $usrUserId,
            columnKey: 'mst_mission_event_daily_bonus_id',
            columnValue: $mstMissionId,
            dbCallback: function () use ($usrUserId, $mstMissionId) {
                return UsrMissionEventDailyBonus::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_mission_event_daily_bonus_id', $mstMissionId)
                    ->first();
            },
        );
    }

    /**
     * @return Collection<UsrMissionEventDailyBonusInterface>
     */
    public function getByMstMissionIds(string $usrUserId, Collection $mstMissionIds): Collection
    {
        if ($mstMissionIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstMissionIds) {
                return $cache->filter(function (UsrMissionEventDailyBonusInterface $model) use ($mstMissionIds) {
                    return $mstMissionIds->contains($model->getMstMissionId());
                });
            },
            expectedCount: $mstMissionIds->count(),
            dbCallback: function () use ($usrUserId, $mstMissionIds) {
                return UsrMissionEventDailyBonus::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_mission_event_daily_bonus_id', $mstMissionIds->toArray())
                    ->get();
            },
        );
    }

    /**
     * 報酬受け取り可能なデータを全取得する
     *
     * 期待するデータ数は未知のため、expectedCountにnullを指定する。
     *
     * DB保存実行はUseCaseの最後なので、それまでに変更があったモデルはこのメソッド実行時にはまだDBに反映されていない。
     * そのため、DBに反映されていないが変更があるモデルと、UsrModelManagerに格納されていないモデルの両方を取得する必要がある。
     *
     * @param string $usrUserId
     * @param CarbonImmutable|null $sameTermStartAt リセットされないデータのみを取得するための時間情報
     * @param Collection|null $mstMissionIds ミッションIDで絞る場合の情報
     */
    public function getReceivableRewards(
        string $usrUserId,
        ?CarbonImmutable $sameTermStartAt = null,
        ?Collection $mstMissionIds = null
    ): Collection {
        return $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) {
                return $cache->filter(function (UsrMissionEventDailyBonusInterface $model) {
                    return $model->getStatus() === MissionStatus::CLEAR->value;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId) {
                return UsrMissionEventDailyBonus::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('status', MissionStatus::CLEAR->value)
                    ->get();
            },
        );
    }
}

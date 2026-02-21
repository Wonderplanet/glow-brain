<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * キャッシュ機構は未実装
 */
class SysPvpSeasonRepository
{
    public function __construct()
    {
    }

    private function make(string $id, CarbonImmutable $now): SysPvpSeason
    {
        $model = new SysPvpSeason();
        $model->setId($id);

        $isoWeek = $now->isoWeek();
        $isoWeekYear = $now->isoWeekYear();

        $monday = $now->setISODate($isoWeekYear, $isoWeek);

        $startAt = $monday->setTime(3, 0, 0);
        $endAt = $monday->addDays(6)->setTime(14, 59, 59);
        $closedAt = $monday->addDays(7)->setTime(2, 59, 59);

        $model->setStartAt($startAt);
        $model->setEndAt($endAt);
        $model->setClosedAt($closedAt);

        return $model;
    }

    private function create(string $id, CarbonImmutable $now): SysPvpSeason
    {
        $model = $this->make($id, $now);

        // TODO: セーブは切り離す
        $model->save();

        return $model;
    }

    /**
     * @param string $sysPvpSeasonId
     * @param bool $isThrowError 存在しない場合に例外を投げるかどうか
     * @return SysPvpSeasonEntity|null
     */
    public function findWithError(string $sysPvpSeasonId, bool $isThrowError = false): ?SysPvpSeasonEntity
    {
        $pvpSeason = SysPvpSeason::query()->where('id', $sysPvpSeasonId)->first();
        if ($isThrowError && $pvpSeason === null) {
            throw new GameException(
                ErrorCode::PVP_SESSION_NOT_FOUND,
                'SysPvpSeason is not found.',
            );
        }

        return $pvpSeason ? $pvpSeason->toEntity() : null;
    }

    /**
     * @param string $sysPvpSeasonId
     * @return int
     */
    public function getHeldNumber(string $sysPvpSeasonId): int
    {
        // $idは自動採番により、[西暦 . 0 . 週番号]の形式で生成されるため
        // それ以下のIDのレコード数をカウントすることで、開催回数を取得する。
        return SysPvpSeason::query()
            ->where('id', '<=', $sysPvpSeasonId)
            ->count();
    }

    private function getClosing(CarbonImmutable $now): ?SysPvpSeason
    {
        // 集計期間のシーズンがある場合はそちらを返す
        return SysPvpSeason::query()
            ->where('end_at', '<', $now)
            ->where('closed_at', '>=', $now)
            ->first();
    }

    /**
     * @param string $sysPvpSeasonId
     * @param CarbonImmutable $now
     * @return ?SysPvpSeasonEntity
     */
    public function getCurrent(
        string $sysPvpSeasonId,
        CarbonImmutable $now,
        bool $isThrowError = true
    ): ?SysPvpSeasonEntity {
        // 集計期間のシーズンがある場合はそちらを返す
        $closingSysPvPSeason =  $this->getClosing($now);
        if (! is_null($closingSysPvPSeason)) {
            return $closingSysPvPSeason->toEntity();
        }

        // 集計期間でない場合は指定したIdで取得
        return $this->findWithError($sysPvpSeasonId, $isThrowError);
    }

    /**
     * DBにあれば取得し、なければインスタンスを生成してDB更新した後に返す
     *
     * @param string $sysPvpSeasonId
     * @param CarbonImmutable $now
     * @return SysPvpSeasonEntity
     */
    public function getCurrentOrCreate(
        string $sysPvpSeasonId,
        CarbonImmutable $now
    ): SysPvpSeasonEntity {
        // 集計期間がない場合は指定したIdで取得or生成
        $pvpSeason = $this->getCurrent($sysPvpSeasonId, $now, false);
        if ($pvpSeason === null) {
            $pvpSeason = $this->create($sysPvpSeasonId, $now)->toEntity();
        }
        return $pvpSeason;
    }

    /**
     * DBにあれば取得し、なければDB更新なしでインスタンスを生成して返す
     *
     * @param string $sysPvpSeasonId
     * @param CarbonImmutable $now
     * @return SysPvpSeasonEntity
     */
    public function getCurrentOrMake(
        string $sysPvpSeasonId,
        CarbonImmutable $now
    ): SysPvpSeasonEntity {
        $pvpSeason = $this->getCurrent($sysPvpSeasonId, $now, false);
        if ($pvpSeason === null) {
            $pvpSeason = $this->make($sysPvpSeasonId, $now)->toEntity();
        }
        return $pvpSeason;
    }

    /**
     * 指定Idの一つ前のデータを取得
     */
    public function getPrevious(string $sysPvpSeasonId, bool $isThrowError = true): ?SysPvpSeasonEntity
    {
        // idは日付による自動採番なのでそれより前で一番未来のidを取得
        $pvpSeason = SysPvpSeason::query()
            ->where('id', '<', $sysPvpSeasonId)
            ->orderByDesc('id')
            ->first();

        if ($isThrowError && $pvpSeason === null) {
            // 過去の開催情報が無い
            throw new GameException(
                ErrorCode::PVP_SESSION_NOT_FOUND,
                'SysPvpSeason is not found for previous season.',
            );
        }

        return $pvpSeason ? $pvpSeason->toEntity() : null;
    }

    public function getById(
        string $sysPvpSeasonId,
    ): ?SysPvpSeasonEntity {
        $pvpSeason = SysPvpSeason::query()
            ->where('id', $sysPvpSeasonId)
            ->first();

        return $pvpSeason ? $pvpSeason->toEntity() : null;
    }

    public function getByIds(
        Collection $sysPvpSeasonIds,
    ): Collection {
        $pvpSeasons = SysPvpSeason::query()
            ->whereIn('id', $sysPvpSeasonIds)
            ->get();

        return $pvpSeasons->mapWithKeys(function (SysPvpSeason $model) {
            return [$model->id => $model->toEntity()];
        });
    }

    public function countSeasonsAfter(
        string $sysPvpSeasonId,
        string $lastPlayedSysPvpSeasonId,
    ): int {
        return SysPvpSeason::query()
            ->where('id', '<=', $sysPvpSeasonId)
            ->where('id', '>', $lastPlayedSysPvpSeasonId)
            ->count();
    }

    // 個数を指定した版のgetPrevious
    public function getPreviousWithCount(string $sysPvpSeasonId, int $count): Collection
    {
        if ($count <= 0) {
            return collect();
        }

        $pvpSeasons = SysPvpSeason::query()
            /**
             * todo: DB上のIDカラムの型が文字列なのでやばい
             * 本番環境では採番上10000年にならない限りは問題ないがテストデータが1,2,3...の場合がある
             */
            ->where('id', '<', $sysPvpSeasonId)
            ->orderByDesc('id')
            ->limit($count)
            ->get();

        return $pvpSeasons->mapWithKeys(function (SysPvpSeason $model) {
            return [$model->id => $model->toEntity()];
        });
    }
}

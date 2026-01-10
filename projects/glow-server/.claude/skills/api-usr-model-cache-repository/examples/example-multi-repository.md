# 実装例: UsrExchangeLineupRepository (MultiCacheRepository)

## 概要

UsrExchangeLineupRepositoryは、usr_exchange_lineupテーブル（1ユーザー複数レコード）のRepositoryです。

**特徴:**
- UsrModelMultiCacheRepositoryを継承
- saveModelsを実装（必須）
- cachedGetMany, cachedGetOneWhereメソッドを使用
- ユニークキー: (usr_user_id, mst_exchange_lineup_id, mst_exchange_id)

## 完全なコード例

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Repositories;

use App\Domain\Exchange\Models\UsrExchangeLineup;
use App\Domain\Exchange\Models\UsrExchangeLineupInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrExchangeLineupRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrExchangeLineup::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrExchangeLineup $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_exchange_lineup_id' => $model->getMstExchangeLineupId(),
                'mst_exchange_id' => $model->getMstExchangeId(),
                'trade_count' => $model->getTradeCount(),
                'reset_at' => $model->getResetAt(),
            ];
        })->toArray();

        UsrExchangeLineup::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_exchange_lineup_id', 'mst_exchange_id'],
            ['trade_count', 'reset_at'],
        );
    }

    /**
     * ユーザーID、ラインナップID、交換所IDで取得
     */
    public function get(
        string $usrUserId,
        string $mstExchangeLineupId,
        string $mstExchangeId
    ): ?UsrExchangeLineupInterface {
        $results = $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstExchangeLineupId, $mstExchangeId) {
                return $cache->filter(
                    function (UsrExchangeLineupInterface $model) use ($mstExchangeLineupId, $mstExchangeId) {
                        return $model->getMstExchangeLineupId() === $mstExchangeLineupId
                            && $model->getMstExchangeId() === $mstExchangeId;
                    }
                );
            },
            expectedCount: 1,
            dbCallback: function () use ($usrUserId, $mstExchangeLineupId, $mstExchangeId) {
                return UsrExchangeLineup::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_exchange_lineup_id', $mstExchangeLineupId)
                    ->where('mst_exchange_id', $mstExchangeId)
                    ->get();
            }
        );

        return $results->first();
    }

    /**
     * 新規作成
     */
    private function create(
        string $usrUserId,
        string $mstExchangeLineupId,
        string $mstExchangeId,
        CarbonImmutable $now
    ): UsrExchangeLineupInterface {
        $model = new UsrExchangeLineup();
        $model->usr_user_id = $usrUserId;
        $model->mst_exchange_lineup_id = $mstExchangeLineupId;
        $model->mst_exchange_id = $mstExchangeId;
        $model->trade_count = 0;
        $model->reset_at = $now->toDateTimeString();

        $this->syncModel($model);

        return $model;
    }

    /**
     * ユーザーID、ラインナップID、交換所IDで取得（なければ新規作成）
     */
    public function getOrCreate(
        string $usrUserId,
        string $mstExchangeLineupId,
        string $mstExchangeId,
        CarbonImmutable $now
    ): UsrExchangeLineupInterface {
        $model = $this->get($usrUserId, $mstExchangeLineupId, $mstExchangeId);
        if ($model === null) {
            $model = $this->create($usrUserId, $mstExchangeLineupId, $mstExchangeId, $now);
        }
        return $model;
    }

    /**
     * 指定した交換所IDに対応する交換履歴を取得
     */
    public function getListByMstExchangeIds(string $usrUserId, Collection $mstExchangeIds): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            expectedCount: null,
            cacheCallback: function (Collection $cache) use ($mstExchangeIds) {
                return $cache->filter(
                    function (UsrExchangeLineupInterface $model) use ($mstExchangeIds) {
                        return $mstExchangeIds->contains($model->getMstExchangeId());
                    }
                );
            },
            dbCallback: function () use ($usrUserId, $mstExchangeIds) {
                return UsrExchangeLineup::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_exchange_id', $mstExchangeIds)
                    ->get();
            }
        );
    }
}
```

## ポイント解説

### 1. saveModelsの実装（必須）

MultiCacheRepositoryでは、必ずsaveModelsを実装してください。

```php
protected function saveModels(Collection $models): void
{
    $upsertValues = $models->map(function (UsrExchangeLineup $model) {
        return [
            'id' => $model->getId(),
            'usr_user_id' => $model->getUsrUserId(),
            'mst_exchange_lineup_id' => $model->getMstExchangeLineupId(),
            'mst_exchange_id' => $model->getMstExchangeId(),
            'trade_count' => $model->getTradeCount(),
            'reset_at' => $model->getResetAt(),
        ];
    })->toArray();

    UsrExchangeLineup::query()->upsert(
        $upsertValues,
        ['usr_user_id', 'mst_exchange_lineup_id', 'mst_exchange_id'],  // ユニークキー
        ['trade_count', 'reset_at'],  // 更新対象カラム
    );
}
```

### 2. cachedGetManyの使用（expectedCount指定）

getメソッドでは、expectedCount: 1を指定して、1レコードのみ取得しています。

```php
public function get(...): ?UsrExchangeLineupInterface
{
    $results = $this->cachedGetMany(
        $usrUserId,
        cacheCallback: function (Collection $cache) use ($mstExchangeLineupId, $mstExchangeId) {
            return $cache->filter(
                function (UsrExchangeLineupInterface $model) use ($mstExchangeLineupId, $mstExchangeId) {
                    return $model->getMstExchangeLineupId() === $mstExchangeLineupId
                        && $model->getMstExchangeId() === $mstExchangeId;
                }
            );
        },
        expectedCount: 1,  // 1レコードのみ取得を期待
        dbCallback: function () use ($usrUserId, $mstExchangeLineupId, $mstExchangeId) {
            return UsrExchangeLineup::query()
                ->where('usr_user_id', $usrUserId)
                ->where('mst_exchange_lineup_id', $mstExchangeLineupId)
                ->where('mst_exchange_id', $mstExchangeId)
                ->get();
        }
    );

    return $results->first();
}
```

### 3. cachedGetManyの使用（expectedCount未指定）

getListByMstExchangeIdsメソッドでは、expectedCount: nullを指定して、取得数が不明な場合に対応しています。

```php
public function getListByMstExchangeIds(string $usrUserId, Collection $mstExchangeIds): Collection
{
    return $this->cachedGetMany(
        $usrUserId,
        expectedCount: null,  // 取得数が不明
        cacheCallback: function (Collection $cache) use ($mstExchangeIds) {
            return $cache->filter(
                function (UsrExchangeLineupInterface $model) use ($mstExchangeIds) {
                    return $mstExchangeIds->contains($model->getMstExchangeId());
                }
            );
        },
        dbCallback: function () use ($usrUserId, $mstExchangeIds) {
            return UsrExchangeLineup::query()
                ->where('usr_user_id', $usrUserId)
                ->whereIn('mst_exchange_id', $mstExchangeIds)
                ->get();
        }
    );
}
```

### 4. syncModelの使用

createメソッドで、新規作成したモデルをキャッシュに追加しています。

```php
private function create(...): UsrExchangeLineupInterface
{
    $model = new UsrExchangeLineup();
    $model->usr_user_id = $usrUserId;
    $model->mst_exchange_lineup_id = $mstExchangeLineupId;
    $model->mst_exchange_id = $mstExchangeId;
    $model->trade_count = 0;
    $model->reset_at = $now->toDateTimeString();

    $this->syncModel($model);  // キャッシュに追加

    return $model;
}
```

## UsrPvpRepositoryの例

より複雑な例として、UsrPvpRepositoryを紹介します。

```php
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

class UsrPvpRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrPvp::class;

    protected function saveModels(Collection $models): void
    {
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
```

### ポイント: cachedGetOneWhereの使用

特定のカラム値で1レコードのみ取得する場合は、cachedGetOneWhereを使用します。

```php
$model = $this->cachedGetOneWhere(
    $usrUserId,
    'sys_pvp_season_id',  // カラム名
    $sysPvpSeasonId,      // カラム値
    function () use ($usrUserId, $sysPvpSeasonId) {
        return UsrPvp::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();
    },
);
```

## まとめ

MultiCacheRepositoryの実装ポイント:

- ✅ saveModelsを必ず実装（全カラムを記述）
- ✅ cachedGetAll, cachedGetMany, cachedGetOneWhereを使い分け
- ✅ expectedCountを指定してDBアクセスを最適化
- ✅ syncModelで新規作成・更新をキャッシュに反映

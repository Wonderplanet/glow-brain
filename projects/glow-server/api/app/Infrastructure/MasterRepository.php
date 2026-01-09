<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Entities\MasterReleaseVersionEntity;
use App\Domain\Common\Utils\APCuUtility;
use App\Domain\Resource\Mst\Repositories\OprMasterReleaseControlRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MasterRepository
{
    private const CACHE_KEY_PREFIX_MST = 'mst';

    private const CACHE_KEY_SUFFIX_MST_DAY_ACTIVES = 'mst_day_actives';

    /**
     * 期間指定ありのマスタテーブルにて、現在有効なデータを取得する際に利用するタイムゾーン
     * @var string
     */
    private const CACHE_ACTIVES_TIMEZONE = 'Asia/Tokyo';

    /**
     * データベースに保存されている期間指定情報のタイムゾーン
     * @var string
     */
    private const TIMEZONE_DB = 'UTC';

    /**
     * キャッシュの有効期限(秒)
     * @var int
     */
    private const DEFAULT_TTL_SECONDS = 86400; // 1日

    public function __construct() {
    }

    /**
     * =========================
     * 外部クラスから使用するメソッド
     * =========================
     */

    public function get(string $modelClass, ?callable $formatter = null): Collection
    {
        return collect(
            $this->getAll($modelClass, $formatter),
        );
    }

    public function getByColumn(string $modelClass, string $column, mixed $value, ?callable $formatter = null): Collection
    {
        return collect(
            $this->getByBuilder(
                $modelClass::query()->where($column, $value),
                $formatter,
            ),
        );
    }

    /**
     * @param string $modelClass
     * @param array<string, mixed> $conditions key: 列名, value: 値 の連想配列
     */
    public function getByColumns(string $modelClass, array $conditions): Collection
    {
        $builder = $modelClass::query();
        foreach ($conditions as $column => $value) {
            $builder->where($column, $value);
        }

        return collect(
            $this->getByBuilder($builder),
        );
    }

    /**
     * 期間ありのデータの内で、現在日時が含まれる1日間で1秒以上有効になるデータのみを取得しキャッシュする。
     * 1日間の判定は、引数の$nowUtcをCACHE_ACTIVES_TIMEZONEで変換した後の日時情報で行う。
     *
     * 例：CACHE_ACTIVES_TIMEZONE='Asia/Tokyo', $nowUtc="2025-02-25 23:00:00"(UTC) の場合
     * $nowをJSTに変換すると、2025-02-26 08:00:00 となるので、
     * JSTで、2025-02-26 00:00:00 ~ 2025-02-26 23:59:59 の間で、1秒でも有効になるデータのみを取得しキャッシュする。
     *
     * @param CarbonImmutable $nowUtc 現在日時(UTC)
     * @return Collection<string, mixed> key: id, value: entity
     */
    public function getDayActives(
        string $modelClass,
        CarbonImmutable $nowUtc,
        string $startAtColumn = 'start_at',
        string $endAtColumn = 'end_at',
    ): Collection {
        $now = $nowUtc->setTimezone(self::CACHE_ACTIVES_TIMEZONE);
        $cacheKey = $this->createCacheKey(
            $modelClass,
            sprintf(
                '%s:%s',
                self::CACHE_KEY_SUFFIX_MST_DAY_ACTIVES,
                $now->format('Ymd'),
            ),
        );

        /** @var Collection<string, mixed> $entities */
        $entities = $this->getOrCreateCache(
            $modelClass,
            $cacheKey,
            function () use ($modelClass, $now, $startAtColumn, $endAtColumn) {
                return $modelClass::query()
                    ->where($startAtColumn, '<=', $now->endOfDay()->setTimezone(self::TIMEZONE_DB))
                    ->where($endAtColumn, '>=', $now->startOfDay()->setTimezone(self::TIMEZONE_DB))
                    ->get()
                    ->reduce(function ($carry, $model) {
                        $entity = $model->toEntity();
                        $carry[$entity->getId()] = $entity;
                        return $carry;
                    }, []);
            },
        );

        return collect($entities);
    }

    /**
     * =========================
     * マスタデータキャッシュ機構の実装
     * =========================
     *
     * クラス内部でのみ使用するメソッド。外部からの使用は禁止。
     */

    /**
     * 全件のキャッシュを取得、作成
     * キャッシュキーは自動生成
     *
     * idをキーとした連想配列でキャッシュに保存する
     *
     * @param string $modelClass
     * @return array<string, mixed> key: id, value: entity
     */
    private function getAll(string $modelClass, ?callable $formatter = null): mixed
    {
        $builder = $modelClass::query();

        return $this->getOrCreateCache(
            $modelClass,
            $builder->toRawSql(),
            function () use ($builder, $formatter) {
                $entities = $builder->get()->map->toEntity();
                if ($formatter === null) {
                    $entities = $entities->keyBy->getId();
                } else {
                    $entities = $formatter($entities);
                }

                return $entities;
            },
        );
    }

    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは$builderから自動生成
     * @param Builder $builder
     * @return array<mixed>
     */
    private function getByBuilder(Builder $builder, ?callable $formatter = null): mixed
    {
        return $this->getOrCreateCache(
            $builder->getModel()::class,
            $builder->toRawSql(),
            function () use ($builder, $formatter) {
                // @phpstan-ignore-next-line
                $entities = $builder->get()->map->toEntity();

                if ($formatter !== null) {
                    $entities = $formatter($entities);
                }

                return $entities;
            },
        );
    }

    /**
     * キャッシュを取得、作成する
     * @return mixed
     */
    private function getOrCreateCache(
        string $modelClass,
        string $suffixKey,
        callable $closure,
    ): mixed {
        $cacheKey = $this->createCacheKey($modelClass, $suffixKey);

        $cache = APCuUtility::getCache($cacheKey);
        if (!is_null($cache)) {
            return $cache;
        }

        $entities = $closure();

        APCuUtility::saveCacheWithTTL($cacheKey, $entities, self::DEFAULT_TTL_SECONDS);

        return $entities;
    }

    /**
     * @param string $modelClass
     * @param string $suffixKey
     * @return string
     */
    private function createCacheKey(string $modelClass, string $suffixKey): string
    {
        return sprintf(
            ':%s_%s:%s',
            self::CACHE_KEY_PREFIX_MST,
            $this->getTableName($modelClass),
            md5(
                config('database.connections.mst.database')
                . $suffixKey
            ),
        );
    }

    /**
     * テーブル名を取得
     * @param string $modelClass
     * @return string
     */
    private function getTableName(string $modelClass): string
    {
        return class_basename($modelClass);
    }
}

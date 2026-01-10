<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Services;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Cache\Utils\RedisCacheUtility;

/**
 * Redisキャッシュの機能を提供するサービス
 */
class RedisCacheService
{
    // キャッシュキー
    private const CACHE_KEY_MANAGEMENT_DATA = 'mng';
    private const CACHE_KEY_MANAGEMENT_REFLOG = 'mng_ref';

    private const REFLOG_NUM = 2;

    public function __construct()
    {
    }

    /**
     * 全件のキャッシュを取得、作成
     * キャッシュキーは自動生成
     * @param string $modelClass
     * @param bool $isDeleteCache
     * @return Collection<int, mixed>
     */
    public function getRedisCacheAll(string $modelClass, bool $isDeleteCache = false): Collection
    {
        $builder = $modelClass::query();
        $suffixKey = $builder->toRawSql();
        return $this->getCacheAndCreateCache(
            $modelClass,
            $suffixKey,
            function () use ($builder): Collection {
                $collection = $builder->get();
                return $collection->map(function ($row): mixed {
                    return $row->toEntity();
                });
            },
            $isDeleteCache
        );
    }

    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは引数で渡したものを使用
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @return mixed
     */
    public function getRedisCacheByCustomSearchConditionsWithCustomKey(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
    ): mixed {
        return $this->getCacheAndCreateCache(
            $modelClass,
            $customSuffixKey,
            $closure
        );
    }

    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは$builderから自動生成
     *
     * @param Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     * @return Collection<int, mixed>
     */
    public function getRedisCacheByCustomSearchConditions(Builder $builder): Collection
    {
        $suffixKey = $builder->toRawSql();
        return $this->getCacheAndCreateCache(
            $builder->getModel()::class,
            $suffixKey,
            function () use ($builder): Collection {
                $collection = $builder->get();
                /** @var Collection<int, mixed> $collection */
                return $collection->map(function ($row) {
                    return $row->toEntity();
                });
            },
        );
    }

    /**
     * キャッシュを取得、作成する
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @param bool $isDeleteCache // キャッシュにデータがあってもデータの取得とキャッシュ作成を行うかどうか
     * @return Collection<int, mixed>
     */
    private function getCacheAndCreateCache(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
        bool $isDeleteCache = false,
    ): Collection {
        // キャッシュ用のキーを作成する
        $model = $modelClass;
        $suffixKey = $customSuffixKey;
        $cacheKey = $this->createRedisCacheKey($model, $suffixKey);
        // キャッシュ履歴用のキーを作成
        $reflogCacheKey = $this->createRedisReflogCacheKey($model, $suffixKey);

        if ($isDeleteCache) {
            // 強制的にキャッシュを作成する場合、古いキーのキャッシュは全削除する
            $this->deleteReflogCache($reflogCacheKey);
        }

        // Redisキャッシュがあればそれを返す
        $cacheData = RedisCacheUtility::getCache($cacheKey);
        if (!is_null($cacheData)) {
            return $cacheData;
        }

        // データの取得を実行
        $entities = $closure();

        // キャッシュ履歴を更新
        $deleteEntityCacheKeys = $this->updateReflogCache($reflogCacheKey, $cacheKey);

        // エンティティをRedisキャッシュに保存
        RedisCacheUtility::saveCache($cacheKey, $entities);

        // 履歴から削除された古いentityのキャッシュを削除
        if (count($deleteEntityCacheKeys) > 0) {
            foreach ($deleteEntityCacheKeys as $deleteEntityCacheKey) {
                RedisCacheUtility::deleteCache($deleteEntityCacheKey);
            }
        }
        return $entities;
    }

    /**
     * 最新のものから指定個数の履歴を残し、削除したキャッシュのキーを返す
     * @param string $reflogCacheKey
     * @param string $entityCacheKey
     * @return array<string>
     */
    private function updateReflogCache(string $reflogCacheKey, string $entityCacheKey): array
    {
        // 現在時刻を取得
        // サーバーデバック機能のCarbon::setTestNowで操作されてない時刻を取得する
        $now = new DateTime();
        $now->setTimezone(new \DateTimeZone(config('app.timezone')));
        $format = $now->format('Y-m-d H:i:s');

        // 履歴のキャッシュを取得
        /** @var array<string, string>|null */
        $logs = RedisCacheUtility::getCache($reflogCacheKey);

        // 履歴がなければ初回なので保存して終わり
        if (is_null($logs)) {
            RedisCacheUtility::saveCache($reflogCacheKey, [
                $entityCacheKey => $format,
            ]);

            return [];
        }

        $logs[$entityCacheKey] = $format;

        // 時刻でソートして保存するものと削除するキーを分ける
        $collection = collect($logs);
        $sorted = $collection->sortByDesc(function ($product, $key) {
            return strtotime($product);
        });
        $saves = [];
        $deleteEntityCacheKeys = [];
        foreach ($sorted->all() as $key => $time) {
            if (count($saves) < self::REFLOG_NUM) {
                $saves[$key] = $time;
                continue;
            }
            $deleteEntityCacheKeys[] = $key;
        }

        // 履歴のキャッシュを上書き保存
        RedisCacheUtility::saveCache($reflogCacheKey, $saves);

        return $deleteEntityCacheKeys;
    }

    /**
     * 履歴を全削除し、reflogキーも削除する
     * @param string $reflogCacheKey
     * @return void
     */
    private function deleteReflogCache(string $reflogCacheKey): void
    {
        // 履歴のキャッシュを取得
        /** @var array<string, string>|null */
        $logs = RedisCacheUtility::getCache($reflogCacheKey);

        // 履歴がなければ履歴はないのでスキップ
        if (is_null($logs)) {
            return;
        }

        // 履歴内のキャッシュを削除
        foreach ($logs as $key => $time) {
            RedisCacheUtility::deleteCache($key);
        }
        // reflogのキーも削除
        RedisCacheUtility::deleteCache($reflogCacheKey);
    }

    /**
     * @param string $modelClass
     * @param string $suffixKey
     * @return string
     */
    private function createRedisCacheKey(string $modelClass, string $suffixKey): string
    {
        // modelのテーブル名を取得
        $table = $this->getTableName($modelClass);

        // releaseControlと関数、引数情報をmd5でハッシュ化する
        $hashKey = md5($suffixKey);

        return ':' . self::CACHE_KEY_MANAGEMENT_DATA . '_' . $table . ':' . $hashKey;
    }

    /**
     * @param string $modelClass
     * @param string $suffixKey
     * @return string
     */
    private function createRedisReflogCacheKey(string $modelClass, string $suffixKey): string
    {
        // modelのテーブル名を取得
        $table = $this->getTableName($modelClass);

        // releaseControlと関数、引数情報をmd5でハッシュ化する
        $hashKey = md5($suffixKey);

        return ':' . self::CACHE_KEY_MANAGEMENT_REFLOG . ':' . $table . ':' . $hashKey;
    }

    /**
     * テーブル名を取得
     * @param string $modelClass
     * @return string
     */
    private function getTableName(string $modelClass): string
    {
        $model = new $modelClass();
        return $model->getTable();
    }
}

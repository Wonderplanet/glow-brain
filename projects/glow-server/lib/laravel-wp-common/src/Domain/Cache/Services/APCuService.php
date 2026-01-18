<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Services;

use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Cache\Constants\ErrorCode;
use WonderPlanet\Domain\Cache\Utils\APCuUtility;
use WonderPlanet\Domain\MasterAssetRelease\Delegators\MasterReleaseDelegator;

/**
 * APCuの機能を提供するサービス
 */
class APCuService
{
    // キャッシュキー
    private const CACHE_KEY_MASTER_DATA = 'mst';
    private const CACHE_KEY_MASTER_REFLOG = 'mst_ref';

    private const REFLOG_NUM = 2;

    /**
     * @var array<int, string>
     */
    private array $releaseVersionDbNames = [];

    public function __construct(
        private readonly MasterReleaseDelegator $masterReleaseDelegator,
    ) {
    }

    /**
     * 全件のキャッシュを取得、作成
     * キャッシュキーは自動生成
     * @param string $modelClass
     * @param CarbonImmutable $now
     * @return Collection<int, mixed>
     */
    public function getAll(string $modelClass, CarbonImmutable $now): Collection
    {
        $builder = $modelClass::query();
        $suffixKey = $builder->toRawSql();
        return $this->getCacheAndCreateCache(
            $modelClass,
            $suffixKey,
            function () use ($builder) {
                $collection = $builder->get();
                return $collection->map(function ($row) {
                    return $row->toEntity();
                });
            },
            $now,
        );
    }

    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは引数で渡したものを使用
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @param CarbonImmutable $now
     * @return mixed
     */
    public function getByCustomSearchConditionsWithCustomKey(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
        CarbonImmutable $now,
    ): mixed {
        return $this->getCacheAndCreateCache(
            $modelClass,
            $customSuffixKey,
            $closure,
            $now,
        );
    }

    /**
     * 任意条件のキャッシュを作成、取得する
     * キャッシュキーは$builderから自動生成
     *
     * @param Builder<covariant \Illuminate\Database\Eloquent\Model> $builder
     * @param CarbonImmutable $now
     * @return mixed
     */
    public function getByCustomSearchConditions(Builder $builder, CarbonImmutable $now): mixed
    {
        $suffixKey = $builder->toRawSql();
        return $this->getCacheAndCreateCache(
            $builder->getModel()::class,
            $suffixKey,
            function () use ($builder) {
                $collection = $builder->get();
                /** @var Collection<int, mixed> $collection */
                return $collection->map(function ($row) {
                    return $row->toEntity();
                });
            },
            $now,
        );
    }

    /**
     * キャッシュを取得、作成する
     * @param string $modelClass
     * @param string $customSuffixKey
     * @param callable $closure
     * @param CarbonImmutable $now
     * @return mixed
     */
    private function getCacheAndCreateCache(
        string $modelClass,
        string $customSuffixKey,
        callable $closure,
        CarbonImmutable $now,
    ): mixed {
        // キャッシュ用のキーを作成する
        $model = $modelClass;
        $suffixKey = $customSuffixKey;
        $cacheKey = $this->createCacheKey($model, $suffixKey, $now);
        // キャッシュ履歴用のキーを作成
        $reflogCacheKey = $this->createReflogCacheKey($model, $suffixKey);

        // APCuキャッシュがあればそれを返す
        $cacheData = APCuUtility::getCache($cacheKey);
        if (!is_null($cacheData)) {
            return $cacheData;
        }

        // データの取得を実行
        $entities = $closure();

        // キャッシュ履歴を更新
        $deleteEntityCacheKeys = $this->updateReflogCache($reflogCacheKey, $cacheKey);

        // エンティティをAPCuキャッシュに保存
        APCuUtility::saveCache($cacheKey, $entities);

        // 履歴から削除された古いentityのキャッシュを削除
        if (count($deleteEntityCacheKeys) > 0) {
            foreach ($deleteEntityCacheKeys as $deleteEntityCacheKey) {
                APCuUtility::deleteCache($deleteEntityCacheKey);
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
        $logs = APCuUtility::getCache($reflogCacheKey);

        // 履歴がなければ初回なので保存して終わり
        if (is_null($logs)) {
            APCuUtility::saveCache($reflogCacheKey, [
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
        APCuUtility::saveCache($reflogCacheKey, $saves);

        return $deleteEntityCacheKeys;
    }

    /**
     * @param string $modelClass
     * @param string $suffixKey
     * @param CarbonImmutable $now
     * @return string
     */
    private function createCacheKey(string $modelClass, string $suffixKey, CarbonImmutable $now): string
    {
        // テスト環境の場合はmng_master_releases/mng_master_release_versionsがなくても失敗しないように
        // laravelだとユニットテストの場合はtestingを指定する
        // また、テスト環境だとAPCuは基本無効なので全てのキャッシュキーが固定値でも問題なく動作する
        if (
            config('app.env') === 'local_test' ||
            config('app.env') === 'admin_test' ||
            config('app.env') === 'testing'
        ) {
            // octane向けにAPCuをcilモードで動作させている場合、キーが重複するとテストがエラーになる場合がある。
            // 一連のテスト内で複数回キャッシュ生成されると重複してしまうので、setUpなどでキャッシュクリアしても対応できない
            // もともとユニットテストでキャッシュは有効にならない想定でいたので、キャッシュキーをUUIDにして重複しないようにした
            return uniqid(self::CACHE_KEY_MASTER_DATA);
        }

        // 現在のマスターDBの接続先が、配信中のリリース情報から得たマスタDBの接続先と異なっていたら例外を投げる
        $releaseVersionDbNames = $this->getReleaseVersionDbNames($now);
        $currentMstDatabaseConnectionDbName = config('database.connections.mst.database');
        if (!in_array($currentMstDatabaseConnectionDbName, $releaseVersionDbNames, true)) {
            $releaseVersionDbNamesStr = implode(',', $releaseVersionDbNames);
            $message = 'config(database.connections.mst.database) : ' . $currentMstDatabaseConnectionDbName
                . ', releaseVersionDbNames : ' . $releaseVersionDbNamesStr;
            throw new \Exception($message, ErrorCode::MASTER_DATABASE_CONNECTIONS_DIFFERENT);
        }

        // modelのテーブル名を取得
        $table = $this->getTableName($modelClass);

        // Octaneの制限の影響でキャッシュキーは最大で60文字としたい
        // md5のハッシュが32文字＋コロンの33文字なので、接頭辞＋テーブル名で27文字を超える場合は途中でカットする
        $cacheKey = ':' . self::CACHE_KEY_MASTER_DATA . '_' . $table;
        if (mb_strlen($cacheKey) > 27) {
            $cacheKey = mb_substr($cacheKey, 0, 27);
        }
        // リリースバージョンDBと引数情報をmd5でハッシュ化する
        $hashKey = md5($currentMstDatabaseConnectionDbName . '_' . $suffixKey);

        return $cacheKey . ':' . $hashKey;
    }

    /**
     * @param string $modelClass
     * @param string $suffixKey
     * @return string
     */
    private function createReflogCacheKey(string $modelClass, string $suffixKey): string
    {
        // テスト環境の場合はopr_master_release_controlsがなくても失敗しないように
        // laravelだとユニットテストの場合はtestingを指定する
        // また、テスト環境だとAPCuは基本無効なので全てのキャッシュキーが固定値でも問題なく動作する
        if (
            config('app.env') === 'local_test' ||
            config('app.env') === 'admin_test' ||
            config('app.env') === 'testing'
        ) {
            // octane向けにAPCuをcilモードで動作させている場合、キーが重複するとテストがエラーになる場合がある。
            // 一連のテスト内で複数回キャッシュ生成されると重複してしまうので、setUpなどでキャッシュクリアしても対応できない
            // もともとユニットテストでキャッシュは有効にならない想定でいたので、キャッシュキーをUUIDにして重複しないようにした
            return uniqid(self::CACHE_KEY_MASTER_REFLOG);
        }

        // modelのテーブル名を取得
        $table = $this->getTableName($modelClass);
        // Octaneの制限の影響でキャッシュキーは最大で60文字としたい
        // md5のハッシュが32文字＋コロンの33文字なので、接頭辞＋テーブル名で27文字を超える場合は途中でカットする
        $cacheKey = ':' . self::CACHE_KEY_MASTER_REFLOG . ':' . $table;
        if (mb_strlen($cacheKey) > 27) {
            $cacheKey = mb_substr($cacheKey, 0, 27);
        }
        // releaseControlと関数、引数情報をmd5でハッシュ化する
        $hashKey = md5($suffixKey);

        return $cacheKey . ':' . $hashKey;
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

    /**
     * 現在配信中のリリースバージョンDB名を取得
     *
     * @param CarbonImmutable $now
     * @return array<int, string>
     * @throws \Exception
     */
    private function getReleaseVersionDbNames(CarbonImmutable $now): array
    {
        if ($this->releaseVersionDbNames !== []) {
            return $this->releaseVersionDbNames;
        }

        $masterReleaseVersionEntities = $this->masterReleaseDelegator
            ->getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection($now);
        $this->releaseVersionDbNames = $masterReleaseVersionEntities->map(function (array $map) {
            /** @var \WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity $entity */
            $entity = $map['entity'];
            return $entity->getDbName();
        })->toArray();

        return $this->releaseVersionDbNames;
    }
}

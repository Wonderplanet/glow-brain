<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\DatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;

/**
 * マスターデータインポートv2管理ツール用
 */
class DatabaseImportService
{
    private string $databaseCsvDir = 'wp_master_asset_release_admin.databaseCsvDir';

    /**
     * dbNameをキー、masterSchemaVersionを値にもつコレクション
     *
     * @var Collection
     */
    private Collection $masterSchemaVersions;

    public function __construct(
        readonly private ClassSearchService $classSearchService,
        readonly private CsvConvertService $csvConvertService,
        readonly private CsvLoadService $csvLoadService,
        readonly private MasterDataDBOperator $masterDataDBOperator,
    ) {
        $this->masterSchemaVersions = collect();
    }

    /**
     * マスターのリリースバージョンごとのDBを用意してインポートを実行する
     *
     * @param string $importId
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @param array $newServerDbHashMap
     * @return Collection
     * @throws \Exception
     */
    public function import(
        string $importId,
        MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity,
        array $newServerDbHashMap
    ): Collection {
        \Log::info('import start from: ' . config($this->databaseCsvDir) . '/' . $importId);

        // 中間CSVロード
        $mstEntities = $this->csvLoadService->loadDatabaseCsv(config($this->databaseCsvDir) . "/{$importId}/mst/");
        $oprEntities = $this->csvLoadService->loadDatabaseCsv(config($this->databaseCsvDir) . "/{$importId}/opr/");

        // csvファイル名に沿ってマスターDBを作成する
        \Log::info('createDb start');
        $this->createDb($mstEntities, $oprEntities, $mngMasterReleaseKeyEntity, $newServerDbHashMap);

        // インポート実行
        \Log::info('dbImport start');
        $this->dbImport($mstEntities, $newServerDbHashMap);
        $this->dbImport($oprEntities, $newServerDbHashMap);

        // インポートしたマスターDBのdumpファイルを生成する
        \Log::info('generateMasterDump start');
        $this->generateMasterDump($importId, $mngMasterReleaseKeyEntity, $newServerDbHashMap);

        // マスターデータに関連するキャッシュをクリアする
         Cache::tags('mst')->flush();

        \Log::info('import done importId: ' . $importId);

         // リリースバージョンDBのハッシュ情報を返す
         return $this->masterSchemaVersions;
    }

    /**
     * リリースバージョンごとのDBを作成する
     *
     * @param DatabaseCsvEntity[] $mstEntities
     * @param DatabaseCsvEntity[] $oprEntities
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @param array $newServerDbHashMap
     * @return void
     * @throws \Exception
     */
    private function createDb(
        array $mstEntities,
        array $oprEntities,
        MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity,
        array $newServerDbHashMap
    ): void {
        // 現時点で作成されているマスターDB情報を取得
        $databases = array_filter(
            $this->masterDataDBOperator->showDatabases(),
            fn ($dbName)=> $this->masterDataDBOperator->isMasterDataDBName($dbName)
        );

        // 生成したdatabase_csv/importId/のファイルを元にリリースキー+serverDbHashごとのDB名を取得
        $createdDbNames = [];
        $notExistDbNames = [];
        $getMasterDbName = function($releaseKey) use ($databases, &$createdDbNames, &$notExistDbNames, $newServerDbHashMap) {
            $dbName = $this->masterDataDBOperator->getMasterDbName($releaseKey, $newServerDbHashMap);

            // $versionのマスターDBが存在するかチェック
            $isCreatedDatabase = in_array($dbName, $databases, true);
            if ($isCreatedDatabase && !in_array($dbName, $createdDbNames, true)) {
                // 作成済みかつ$createdDbNamesに保存してなければ保存
                $createdDbNames[] = $dbName;
            }
            if (!$isCreatedDatabase && !in_array($dbName, $notExistDbNames, true)) {
                // 存在しないかつ$notExistDbNamesに保存してなければ保存
                $notExistDbNames[] = $dbName;
            }
        };
        foreach ($mstEntities as $mstEntity) {
            $getMasterDbName($mstEntity->getReleaseKey());
        }
        foreach ($oprEntities as $oprEntity) {
            $getMasterDbName($oprEntity->getReleaseKey());
        }

        // マスターDBのコピー元にするDB名を決める
        // 初期値はデフォルトのDB名
        $copyTarget = config('app.env');
        // 現在配信中または準備中の情報をもとにdbNameを取得する
        [
            'releaseKey' => $releaseKey,
            'serverDbHashMap' => $serverDbHashMap,
        ] = $mngMasterReleaseKeyEntity->getMasterDbNameParameter();
        $dbName = $this->masterDataDBOperator->getMasterDbName($releaseKey, $serverDbHashMap);

        // 未作成DBの作成(既存DBをコピー＆マイグレーション実行)
        foreach ($notExistDbNames as $newDbName) {
            $this->masterDataDBOperator->copyDatabase($copyTarget, $newDbName);
            $this->masterDataDBOperator->migrate($newDbName, config('wp_master_asset_release_admin.migrationFileDir'));
            $masterSchemaVersion = $this->masterDataDBOperator->getMasterSchemaVersion($newDbName);
            $this->masterSchemaVersions->put($newDbName, $masterSchemaVersion);
        }

        // 作成済みDBにマイグレーションを実行
        foreach ($createdDbNames as $createdDbName) {
            // すでにマイグレート済みのDBにマイグレートする場合があるので一度削除する
            $this->masterDataDBOperator->drop($createdDbName);
            $this->masterDataDBOperator->copyDatabase($copyTarget, $createdDbName);
            $this->masterDataDBOperator->migrate($createdDbName, config('wp_master_asset_release_admin.migrationFileDir'));
            $masterSchemaVersion = $this->masterDataDBOperator->getMasterSchemaVersion($createdDbName);
            $this->masterSchemaVersions->put($createdDbName, $masterSchemaVersion);
        }
    }

    /**
     * インポート実行
     *
     * @param DatabaseCsvEntity[] $entities
     * @param array $newServerDbHashMap
     * @return void
     */
    private function dbImport(array $entities, array $newServerDbHashMap): void
    {
        foreach ($entities as $entity) {
            \Log::info('importing ' . $entity->getTitle() . ' ReleaseKey: ' . $entity->getReleaseKey());
            $dbName = $this->masterDataDBOperator->getMasterDbName($entity->getReleaseKey(), $newServerDbHashMap);
            $this->masterDataDBOperator->setConnection($dbName);

            // モデルのDB操作を使ってcsvから読んだデータをupsert
            $clazz = $this->classSearchService->createMasterModelClass($entity->getTitle());
            $tableName = $clazz->getTable();
            $primaryKeys = $this->masterDataDBOperator->showPrimaryKeys($tableName);
            $data = $this->csvConvertService->convertToDataArrayUsePlaceholder($entity->getData());
            $this->masterDataDBOperator->truncate($clazz);
            $this->masterDataDBOperator->upsert($clazz, $data, $primaryKeys);
        }
        \Log::info('dbImport done');
    }

    /**
     * インポートしたマスターDBのdumpファイルを作成する
     *
     * @param string $importId
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @param array $newServerDbHashMap
     * @return void
     * @throws \Exception
     */
    private function generateMasterDump(string $importId, MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity, array $newServerDbHashMap): void
    {
        foreach ($mngMasterReleaseKeyEntity->getReleaseKeys() as $releaseKey) {
            // 対象DBのmysqldumpを実行
            $this->masterDataDBOperator->generateMasterDBDump($importId, $releaseKey, $newServerDbHashMap);
        }
    }

    /**
     * 環境間インポート用データインポートを実行
     *
     * @param string $releaseKey
     * @param array $serverDbHashMap
     * @param string $mysqlDumpFilePath
     * @return void
     * @throws \Exception
     */
    public function importFromEnvironment(
        string $releaseKey,
        array $serverDbHashMap,
        string $mysqlDumpFilePath
    ): void {
        \Log::info('importFromEnvironment start');

        // 現時点で作成されているマスターDB情報を取得
        $databases = array_filter(
            $this->masterDataDBOperator->showDatabases(),
            fn ($dbName)=> $this->masterDataDBOperator->isMasterDataDBName($dbName)
        );

        $dbName = $this->masterDataDBOperator->getMasterDbName($releaseKey, $serverDbHashMap);
        if (in_array($dbName, $databases, true)) {
            // 同名のDBが存在していたら破棄する
            \Log::info('importFromEnvironment dropDb dbName: ' . $dbName);
            $this->masterDataDBOperator->drop($dbName);
        }

        // DB作成＆mysqldumpのデータを復元
        $this->masterDataDBOperator->copyDatabaseFromFilepath($dbName, $mysqlDumpFilePath);
        \Log::info('importFromEnvironment done dbName: ' . $dbName);
    }
}

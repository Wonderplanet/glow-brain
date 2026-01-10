<?php

namespace App\Services\MasterData;

use App\Constants\Database;
use App\Constants\SpreadSheetLabel;
use App\Entities\MasterData\ReleaseControlCsvEntity;
use App\Operators\MasterDataDBOperator;
use App\Services\MasterData\ClassSearchService;
use App\Services\MasterData\CsvConvertService;
use App\Services\MasterData\CsvLoadService;
use Illuminate\Support\Facades\Cache;

class DatabaseImportService
{
    private ClassSearchService $classSearchService;
    private CsvConvertService $csvConvertService;
    private CsvLoadService $csvLoadService;
    public function __construct()
    {
        $this->classSearchService = new ClassSearchService();
        $this->csvConvertService = new CsvConvertService();
        $this->csvLoadService = new CsvLoadService();
    }

    public function import(): void
    {
        // 中間CSVロード
        $mstEntities = $this->csvLoadService->loadDatabaseCsv(config('admin.databaseCsvDir'). '/mst/');
        $oprEntities = $this->csvLoadService->loadDatabaseCsv(config('admin.databaseCsvDir'). '/opr/');
        $this->dbImport($mstEntities);
        $this->dbImport($oprEntities);

        // マスターデータに関連するキャッシュをクリアする
        // Cache::tags('mst')->flush();

        // 全部入ったら、ReleaseControlのデータ更新
        // $database->setConnection(Database::TIDB_CONNECTION);
        // $currentMasterDB = $database->current(Database::TIDB_CONNECTION);
        // if (is_null($releaseKey)) return;
        // $releaseKey = new ReleaseControlCsvEntity($releaseKey->getData(), $releaseKey->getHash());
        // $insert = [];
        // $model = new OprMasterReleaseControl;
        // foreach ($releaseKey->getAvailableData() as $key => $data) {
        //     $insert[] = [
        //         'id' => $model->newUniqueId(), // bulkInsertのため
        //         'release_key' => $key,
        //         'hash' => $releaseKey->getHash(),
        //         'release_at' => $data[SpreadSheetLabel::START_AT_COLUMN],
        //         'release_description' => $data[SpreadSheetLabel::DESCRIPTION_COLUMN].': add by admin',
        //         'deleted_at' => null, // 削除済みのものは復活させる
        //     ];
        // }
        // OprMasterReleaseControl::on(Database::TIDB_CONNECTION)->upsert($insert, ['release_key', 'hash']);

        // // 有効でなくなった過去と未来のDatabaseのドロップ, ただし直前まで使用していたDBはそのままにする
        // if (config('app.env') === 'local' || config('app.env') === 'develop') return; // ローカル, develop環境は共有しているので削除しない
        // if (is_null($currentMasterDB)) return; // 今までのMasterDBが無い場合は削除する必要もない
        // $oldDbs = array_filter($database->showDatabases(), function ($db) use ($currentMasterDB, $insertMasterDB) {
        //     return str_starts_with($db, Database::MASTER_DATA_DB_PREFIX) &&
        //         $db !== $currentMasterDB &&
        //         !in_array($db, $insertMasterDB);
        // });
        // foreach ($oldDbs as $dbName) {
        //     $versions = explode('_', $dbName); // $dbname: mst_230101_1234abcd
        //     $key = $versions[1];
        //     $hash = $versions[2];
        //     OprMasterReleaseControl::on(Database::TIDB_CONNECTION)
        //         ->where('release_key', $key)->where('hash', $hash)
        //         ->delete(); // soft delete
        //     $database->drop($dbName);
        // }
    }

    public function dbImport(array $entities) {
        // Entityのバージョンが指すデータベースにcsvインポート
        $database = new MasterDataDBOperator();
        $releaseKey = null;
        $releaseKeyCount = 0;
        $insertMasterDB = [];
        foreach ($entities as $entity) {
            $dbName = $database->getMasterDbName($entity->getVersion());
            $database->setConnection($dbName);
            $insertMasterDB[] = $dbName;

            // モデルのDB操作を使ってcsvから読んだデータをupsert
            $clazz = $this->classSearchService->createMasterModelClass($entity->getTitle());
            if (is_null($clazz)) {
                continue;
            }
            $tableName = $clazz->getTable();
            $primaryKeys = $database->showPrimaryKeys($tableName);
            $data = $this->csvConvertService->convertToDataArrayUsePlaceholder($entity->getData());
            $database->truncate($clazz);
            foreach (array_chunk($data, 1000) as $chunk) {
                $database->upsert($clazz, $chunk, $primaryKeys);
            }

            // 後続の処理のため、ReleaseKeyのentityのうち、最もデータが多い（release_keyが最多）のものを確保
            // if ($entity->getTitle() === SpreadSheetLabel::RELEASE_KEY_SHEET_NAME && count($entity->getData()) > $releaseKeyCount) {
            //     $releaseKey = $entity;
            //     $releaseKeyCount = count($entity->getData());
            // }
        }
    }
}

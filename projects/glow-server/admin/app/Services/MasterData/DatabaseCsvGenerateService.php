<?php

namespace App\Services\MasterData;

use App\Constants\Database;
use App\Constants\SpreadSheetLabel;
use App\Entities\MasterData\RawDatabaseCsvEntity;
use App\Entities\MasterData\ReleaseControlCsvEntity;
use App\Entities\MasterData\SpreadSheetCsvEntity;
use App\Operators\CSVOperator;
use App\Operators\MasterDataDBOperator;
use App\Services\MasterData\GitCommitService;
use App\Services\MasterData\ClassSearchService;
use App\Services\MasterData\CsvConvertService;
use Illuminate\Support\Str;

class DatabaseCsvGenerateService
{
    private array $createdDbNames = [];

    private CsvConvertService $csvConvertService;
    private GitCommitService $gitCommitService;
    private ClassSearchService $classSearchService;
    public function __construct()
    {
        $this->csvConvertService = new CsvConvertService();
        $this->gitCommitService = new GitCommitService();
        $this->classSearchService = new ClassSearchService();
    }

    public function generateDatabaseCsv(): void
    {
        // 元ファイルを読み込みデータベースに取り込める形式に変換する
        $csv = new CSVOperator();
        [$convertedData, $releaseKey] = $this->generateRawDatabaseCsv();

        // 他スプレッドシートの組み合わせで生成する中間テーブルのCSVを作る
        $csv->cleanup(config('admin.databaseCsvDir'));

        // 現時点のversionのDB準備
        // $database = new MasterDataDBOperator();
        // $databases = array_filter($database->showDatabases(), fn ($dbName)=> $database->isMasterDataDBName($dbName));
        // $versions = $releaseKey->getAvailableVersions();
        // if (empty($versions)) return; // 有効なリリースキーがない
        // foreach ($versions as $version) {
        //     $dbName = $database->getMasterDbName($version);
        //     $copyTargets = array_filter($databases, fn ($name) => $dbName !== $name);
        //     // マイグレーションに時間がかかるためデータベースが存在しない場合は既存マスターをコピーする
        //     if (!in_array($dbName, $databases) && !empty($copyTargets)) {
        //         $database->copyDatabase(array_values($copyTargets)[0], $dbName);
        //     }
        //     $database->migrate($dbName, config('admin.migrationFileDir'));
        //     $this->createdDbNames[] = $dbName;
        // }

        // カラムを見ながらCSV微調整
        $result = [];
        foreach ($convertedData as $data) {
            $result[] = $this->csvConvertService->aggregateJson($data, $releaseKey);
        }

        // version(ReleaseKey+Hash)ごとのCSVの保存
        foreach ($result as $entities) {
            foreach ($entities as $entity) {
                if(substr($entity->getTitle(), 0, 3) === 'Opr') {
                    $filename = config('admin.databaseCsvDir') . '/opr/' . $entity->getTitle() . '.' . $entity->getVersion() . '.csv';
                }
                else {
                    $filename = config('admin.databaseCsvDir') . '/mst/' . $entity->getTitle() . '.' . $entity->getVersion() . '.csv';
                }
                $csv->write($filename, $entity->getData());
            }
        }
    }

    /**
     * DatabaseCsv生成の初期段階の処理
     * マスターバリデーション用に他フェーズでCSV出力する必要があるので、切り出している
     */
    public function generateRawDatabaseCsv(): array
    {
        // 元ファイルの取得
        $files = glob(config('admin.spreadSheetCsvDir') . "/*.csv");

        // 元ファイルのコミット状態
        $gitRevision = $this->gitCommitService->getCurrentHash();

        // バリデーション設定シート
        // $validationSettingSheetNames = config('admin.validationSettingSheetNames');

        // 元ファイルを読み込みデータベースに取り込める形式に変換する
        $csv = new CSVOperator();
        $convertedData = [];
        $releaseKey = null;
        foreach ($files as $filepath) {

            $title = basename($filepath, ".csv");

            // if (!$this->classSearchService->verifyMasterModelClassName($title) && !in_array($title, $validationSettingSheetNames)) {
            if (!$this->classSearchService->verifyMasterModelClassName($title)){
                continue;
            }
            $databaseCsv = $this->csvConvertService->convertCsvEntity($csv->read($filepath), $title);

            // マスターバリデーション用にCSVを出力
            // $csv->write(config('admin.validationCsvDir') . "/" . $databaseCsv->getTitle() . ".csv", $databaseCsv->getData());
            // if (in_array($title, $validationSettingSheetNames)) {
            //     // バリデーション設定シートはCSVの出力のみ行う
            //     continue;
            // }
            $clazz = $this->classSearchService->createMasterModelClass($databaseCsv->getTitle());
            if (is_null($clazz)) {
                continue;
            }
            $databaseCsv->setTableName($clazz->getTable());

            // CSVのフォーマットが適切でない場合は空配列がかえってくるのでスキップする
            if ($databaseCsv->hasData()) {
                $convertedData[] = $databaseCsv;
            }
            if ($title === SpreadSheetLabel::RELEASE_KEY_SHEET_NAME) {
                $releaseKey = new ReleaseControlCsvEntity($databaseCsv->getData(), $gitRevision);
            }
        }

        return [$convertedData, $releaseKey];
    }

    /**
     * 作成したDBを削除して元に戻す
     * 中間CSVはそのままでも次の操作に影響しないので削除しない
     * @return void
     */
    public function rollback(): void
    {
        // DBへの更新を実施する可能性があるのでロールバックで削除しない
        /*
        $database = new MasterDataDBOperator();
        foreach ($this->createdDbNames as $dbName) {
            try {
                $database->drop($dbName);
            } catch (\Throwable $e) {
                // すでに削除されているなどでdropできない場合は無視する
                Log::info('rollback failed: ' . $dbName, ['exception' => $e]);
            }
        }
        */
    }
}

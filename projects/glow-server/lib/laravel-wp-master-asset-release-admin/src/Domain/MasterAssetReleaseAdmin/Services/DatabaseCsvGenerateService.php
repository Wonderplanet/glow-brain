<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\RawDatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\DatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\CSVOperator;

/**
 * マスターデータインポートv2管理ツール用
 */
class DatabaseCsvGenerateService
{
    public function __construct(
        private CsvConvertService $csvConvertService,
        private ClassSearchService $classSearchService,
        private CSVOperator $csvOperator,
    ) {
    }

    private string $configKeyDatabaseCsvDir = 'wp_master_asset_release_admin.databaseCsvDir';
    private string $configKeySpreadSheetCsvDir = 'wp_master_asset_release_admin.spreadSheetCsvDir';

    /**
     * データベース取り込み用のcsvファイルを生成する
     *
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @param string $importId
     * @return array
     * @throws \Exception
     */
    public function generateDatabaseCsv(MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity, string $importId): array
    {
        \Log::info('generateDatabaseCsv start');

        // 元ファイルを読み込みデータベースに取り込める形式に変換する
        $convertedData = $this->generateRawDatabaseCsv();

        // 他スプレッドシートの組み合わせで生成する中間テーブルのCSVを作る
        $this->csvOperator->cleanup(config($this->configKeyDatabaseCsvDir) . "/{$importId}/");

        // カラムを見ながらCSV微調整
        $result = [];
        foreach ($convertedData as $data) {
            $result[] = $this->csvConvertService->aggregateJson($data, $mngMasterReleaseKeyEntity);
        }

        // version(ReleaseKey+GitRevision)ごとのCSVの保存
        foreach ($result as $entities) {
            /** @var DatabaseCsvEntity $entity */
            foreach ($entities as $entity) {
                $dirName = substr($entity->getTitle(), 0, 3) === 'Opr'
                    ? "/{$importId}/opr/"
                    : "/{$importId}/mst/";
                $filename = config($this->configKeyDatabaseCsvDir) . $dirName . $entity->getTitle() . '.' . $entity->getReleaseKey() . '.csv';
                $this->csvOperator->write($filename, $entity->getData());
            }
        }

        \Log::info('generateDatabaseCsv end to: ' . config($this->configKeySpreadSheetCsvDir) . "/{$importId}");

        // 生成したcsvファイルのハッシュ値を取得して返す
        return $this->getDatabaseCsvDataHash($importId, $mngMasterReleaseKeyEntity);
    }

    /**
     * DatabaseCsv生成の初期段階の処理
     * マスターバリデーション用に他フェーズでCSV出力する必要があるので、切り出している
     *
     * @return RawDatabaseCsvEntity[]
     */
    public function generateRawDatabaseCsv(): array
    {
        // 元ファイルの取得
        $files = glob(config($this->configKeySpreadSheetCsvDir) . "/*.csv");

        // 元ファイルを読み込みデータベースに取り込める形式に変換する
        $csv = new CSVOperator();
        $convertedData = [];
        foreach ($files as $filepath) {

            $title = basename($filepath, ".csv");

            if (!$this->classSearchService->verifyMasterModelClassName($title)){
                continue;
            }
            $databaseCsv = $this->csvConvertService->convertCsvEntity($csv->read($filepath), $title);

            // マスターバリデーション用にCSVを出力
            $clazz = $this->classSearchService->createMasterModelClass($databaseCsv->getTitle());
            $databaseCsv->setTableName($clazz->getTable());

            $convertedData[] = $databaseCsv;
        }

        return $convertedData;
    }

    /**
     * 生成したcsvファイルの内容を元にハッシュ値を生成して取得する
     *
     * @param string $importId
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @return array
     * @throws \Exception
     */
    public function getDatabaseCsvDataHash(string $importId, MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity): array
    {
        $csvDataStrings = [];
        foreach ($mngMasterReleaseKeyEntity->getReleaseKeys() as $releaseKey) {
            $mstFiles = $this->getDatabaseCsvFiles("/{$importId}/mst/*.{$releaseKey}.csv");
            $oprFiles = $this->getDatabaseCsvFiles("/{$importId}/opr/*.{$releaseKey}.csv");

            $files = array_merge($mstFiles, $oprFiles);
            $csvData = [];
            foreach ($files as $filepath) {
                $csvData[] = $this->csvOperator->read($filepath);
            }

            $csvDataString = json_encode($csvData);
            $csvDataStrings[$releaseKey] = md5($csvDataString);
        }
        return $csvDataStrings;
    }

    /**
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    protected function getDatabaseCsvFiles(string $filePath): array
    {
        $files = glob(config($this->configKeyDatabaseCsvDir) . $filePath);
        if ($files === false) {
            throw new \Exception('CSVファイルの取得に失敗しました');
        }

        return $files;
    }
}

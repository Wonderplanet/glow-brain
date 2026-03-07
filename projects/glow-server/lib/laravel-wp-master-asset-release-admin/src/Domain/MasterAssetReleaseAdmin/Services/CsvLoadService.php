<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\DatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\CSVOperator;

/**
 * マスターデータインポートv2管理ツール用
 */
class CsvLoadService
{
    private CSVOperator $csv;

    private string $configKeySpreadSheetCsvDir = 'wp_master_asset_release_admin.spreadSheetCsvDir';
    private string $configKeyDatabaseCsvDir = 'wp_master_asset_release_admin.databaseCsvDir';

    public function __construct(CSVOperator $csv = null)
    {
        $this->csv = is_null($csv) ? new CSVOperator() : $csv;
    }

    /**
     * 指定されたファイル名の内、SpreadSheetLabel::COL_NAME_IDENTIFIERを含む行を返す
     *
     * @param string $filename
     * @param string|null $dirPath
     * @return array|null
     */
    public function getSpreadSheetCsvColumnRow(string $filename, string $dirPath = null): ?array
    {
        $dirPath = empty($dirPath) ? config($this->configKeySpreadSheetCsvDir) : $dirPath;
        $file = $dirPath . "/" . $filename;
        if (!file_exists($file)) return null;

        $data = $this->csv->read($file);
        foreach ($data as $row) {
            if (in_array(SpreadSheetLabel::COL_NAME_IDENTIFIER, $row)) return $row;
        }
        return null;
    }

    /**
     * 指定されたディレクトリにあるファイルからデータベースに投入可能なCSVデータを取得してDatabaseCsvEntityとして返す
     * @param ?string $dirPath
     * @return DatabaseCsvEntity[]
     */
    public function loadDatabaseCsv(string $dirPath = null): array
    {
        $dirPath = empty($dirPath) ? config($this->configKeyDatabaseCsvDir) : $dirPath;
        $files = glob($dirPath . "/*.csv");
        $entities = [];

        foreach ($files as $filepath) {
            $fileName = basename($filepath, ".csv"); // [title].[releasekey].csv
            $tmp = explode('.', $fileName);
            if (!isset($tmp[1])) continue;
            $title = $tmp[0];
            $releaseKey = $tmp[1];

            $data = $this->csv->read($filepath);
            $entity = new DatabaseCsvEntity($data, $title);
            $entity->setReleaseKey($releaseKey);
            $entities[] = $entity;
        }
        return $entities;
    }
}

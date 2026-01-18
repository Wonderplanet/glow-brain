<?php

namespace App\Services\MasterData;

use App\Constants\SpreadSheetLabel;
use App\Entities\MasterData\DatabaseCsvEntity;
use App\Operators\CSVOperator;

class CsvLoadService
{
    private CSVOperator $csv;

    public function __construct(CSVOperator $csv = null)
    {
        $this->csv = is_null($csv) ? new CSVOperator() : $csv;
    }

    /**
     * 指定されたファイル名の内、SpreadSheetLabel::COL_NAME_IDENTIFIERを含む行を返す
     * @param string $filename
     * @return string[]
     */
    public function getSpreadSheetCsvColumnRow(string $filename, string $dirPath = null): ?array
    {
        $dirPath = empty($dirPath) ? config('admin.spreadSheetCsvDir') : $dirPath;
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
        $dirPath = empty($dirPath) ? config('admin.databaseCsvDir') : $dirPath;
        $files = glob($dirPath . "/*.csv");
        $entities = [];
        foreach ($files as $filepath) {
            $fileName = basename($filepath, ".csv"); // [title].[releasekey]_[hash].csv
            $tmp = explode('.', $fileName);
            if (!isset($tmp[1])) continue;
            $title = $tmp[0];
            $versions = explode('_', $tmp[1]);
            $releaseKey = $versions[0];
            $gitRevision = $versions[1];

            $data = $this->csv->read($filepath);
            $entity = new DatabaseCsvEntity($data, $title);
            $entity->setReleaseKey($releaseKey);
            $entity->setGitRevision($gitRevision);
            $entities[] = $entity;
        }
        return $entities;
    }
}

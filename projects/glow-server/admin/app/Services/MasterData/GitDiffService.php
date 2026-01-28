<?php

namespace App\Services\MasterData;

use App\Entities\MasterData\GitDiffEntity;
use App\Operators\GitOperator;

class GitDiffService
{
    private GitOperator $git;
    public function __construct()
    {
        $this->git = new GitOperator(config('admin.repositoryUrl'), config('admin.spreadSheetCsvDir'));
    }

    public function checkDiff($currentHash = null): array
    {
        // バリデーション用のディレクトリは除外した上でdiffを取得する
        $validationDirName = config('admin.validationDirName');
        $options = ['--', ":(exclude){$validationDirName}/*"];
        $rawDiff = $this->git->diff($currentHash, $options);

        // filename毎にパースする
        $diff = $this->parseDiff($rawDiff);

        // CSVファイルからヘッダを取得する
        $csvService = new CsvLoadService();
        $headers = [];
        foreach (array_keys($diff) as $filename) {
            $headers[$filename] = $csvService->getSpreadSheetCsvColumnRow($filename);
        }

        // Entityに入れ込む
        $entities = $this->convertParsedDiffToEntity($diff, $headers);

        return $entities;
    }

    public function parseDiff(array $rawDiff): array
    {
        $diff = [];
        $filename = null;
        $index = -1;
        foreach ($rawDiff as $row) {
            // ファイル毎にdiff結果を配列に格納する
            if (preg_match('/^--- a\/(.*)$/', $row, $matches) || preg_match('/^\+\+\+ b\/(.*)$/', $row, $matches)) {
                // 差分のあるファイルは---や+++始まる
                $filename = $matches[1];
                $diff[$filename] = [];
            } else if (preg_match('/^@@ -[0-9]+(,[0-9]+)? \+([0-9]+)(,[0-9]+)? @@/', $row, $matches)) {
                // 変更行は@@ -start,count +start,count @@ で括られる (countが1の場合は省略される)
                $index++;
                $diff[$filename][$index] = [];
                $diff[$filename][$index]['line'] = $matches[2];
                $diff[$filename][$index]['count'] = isset($matches[3]) && !empty($matches[3]) ?
                    str_replace(',', '', $matches[3]) : 1;
            } else if (!is_null($filename) && preg_match('/^-(.*)$/', $row, $matches)) {
                // -で始まる行をold、+で始まる行をnewとして扱う
                $diff[$filename][$index]['old'][] = $matches[1];
            } else if (!is_null($filename) && preg_match('/^\+(.*)$/', $row, $matches)) {
                $diff[$filename][$index]['new'][] = $matches[1];
            }
        }
        return $diff;
    }

    public function convertParsedDiffToEntity(array $parsedDiff, array $headers): array
    {
        $entities = [];
        foreach ($parsedDiff as $filename => $diff) {
            foreach ($diff as $row) {
                if(is_null($headers[$filename])) {
                    continue;
                }
                $entities[] = new GitDiffEntity($filename, $row, $headers[$filename]);
            }
        }
        return $entities;
    }
}

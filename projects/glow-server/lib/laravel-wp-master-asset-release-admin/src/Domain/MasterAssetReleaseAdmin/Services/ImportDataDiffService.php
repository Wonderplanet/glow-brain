<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\ImportDataDiffEntity;

/**
 * マスターデータインポートV2用
 * git diff 情報を元にマスターデータの差分情報を生成するサービス
 */
class ImportDataDiffService
{
    private CsvLoadService $csvLoadService;

    public function __construct()
    {
        $this->csvLoadService = new CsvLoadService();
    }

    /**
     * git diff 情報を元に差分データを生成する
     *
     * @param array $rawDiff
     * @return ImportDataDiffEntity[]
     */
    public function checkDiff(array $rawDiff): array
    {
        // filename毎にパースする
        $diff = $this->parseDiff($rawDiff);

        // パース情報のファイル名をもとに、masterdata_csvのファイルからヘッダー(カラム名)を取得する
        $headers = [];
        foreach (array_keys($diff) as $fileName) {
            $header = $this->csvLoadService->getSpreadSheetCsvColumnRow($fileName);
            $headers[$fileName] = $header;
        }

        // 各マスタごとの差分情報を持つEntityの配列を返す
        return $this->makeDiffData($diff, $headers);
    }

    /**
     * git diff の結果を解析して必要な情報に分類する
     * @see GitOperator::diff()
     *
     * @param array $rawDiff
     * @return array
     */
    private function parseDiff(array $rawDiff): array
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
            } else if (preg_match('/^--- \/dev\/null$/', $row, $matches)) {
                // 「--- /dev/null」と一致する場合は新規ファイルの差分元で、不要な行のためスキップする
                continue;
            } else if (preg_match('/^@@ -[0-9]+(,[0-9]+)? \+([0-9]+)(,[0-9]+)? @@/', $row, $matches)) {
                // 変更行は@@ -start,count +start,count @@ で括られる (countが1の場合は省略される)
                $index++;
                $diff[$filename][$index] = [];
            } else if (!is_null($filename) && preg_match('/^-ENABLE/', $row, $matches)) {
                // `-ENABLE`で始まる行を古いカラム行として扱う
                // oldのカラム行を配列化して取得(カラムに変更がない場合はold_columnsは配列に存在しない)
                $columns = explode(',', $row);
                $columns = array_map(function ($column) {
                    // `-ENABLE`から`-`を削除
                    return $column === '-ENABLE' ? str_replace('-', '', $column) : $column;
                }, $columns);
                $diff[$filename][$index]['old_columns'] = $columns;
            } else if (!is_null($filename) && preg_match('/^-id/', $row, $matches)) {
                // 互換対応
                // `-id`で始まる行を古いカラム行として扱う
                // oldのカラム行を配列化して取得(カラムに変更がない場合はold_columnsは配列に存在しない)
                $columns = explode(',', $row);
                $columns = array_map(function ($column) {
                    // `-ENABLE`から`-`を削除
                    return $column === '-id' ? str_replace('-', '', $column) : $column;
                }, $columns);
                $diff[$filename][$index]['old_columns'] = $columns;
            } else if (!is_null($filename) && preg_match('/^\+ENABLE/', $row, $matches)) {
                // `+ENABLE`で始まる行を新しいカラム行として扱う
                // newのカラム行を配列化して取得(カラムに変更がない場合はnew_columnsは配列に存在しない)
                $columns = explode(',', $row);
                $columns = array_map(function ($column) {
                    // `+ENABLE`から`-`を削除
                    return $column === '+ENABLE' ? str_replace('+', '', $column) : $column;
                }, $columns);
                $diff[$filename][$index]['new_columns'] = $columns;
            } else if (!is_null($filename) && preg_match('/^-(.*)$/', $row, $matches)) {
                // -で始まる行をoldとして扱う
                $rows = str_getcsv($matches[1]);
                if (is_null($rows[0])) {
                    continue;
                }
                $diff[$filename][$index]['old'][] = $rows;
            } else if (!is_null($filename) && preg_match('/^\+(.*)$/', $row, $matches)) {
                // +で始まる行をnewとして扱う
                $rows = str_getcsv($matches[1]);
                $diff[$filename][$index]['new'][] = $rows;
            }
        }

        // 解析データの中でファイル名がMstまたはOprから始まらないデータがある場合は除外する
        return array_filter($diff, function ($data, $filename) {
            return preg_match('/^(Mst|Opr)/', $filename);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 解析した差分データを各マスタごとにImportDataDiffEntityとして返す
     *
     * @param array $parsedDiff
     * @param array $headers
     * @return array<int, ImportDataDiffEntity>
     */
    private function makeDiffData(array $parsedDiff, array $headers): array
    {
        $entities = [];
        foreach ($parsedDiff as $filename => $diff) {
            if(is_null($headers[$filename])) {
                continue;
            }

            $targetFileHeaders = $headers[$filename];
            $fileResult = [
                'modify' => [],
                'new' => [],
                'delete' => [],
            ];
            $oldColumns = [];
            $newColumns = [];
            $structureDiffDeleteData = [];
            $structureDiffAddData = [];
            $modifyRowCountMapByReleaseKey = [];
            $newRowCountMapByReleaseKey = [];
            $deleteRowCountMapByReleaseKey = [];
            foreach ($diff as $row) {
                // 更新前のデータの処理
                // カラム変更があれば更新前のカラム情報を、なければシートのカラム情報をもつ
                $oldColumns = $row['old_columns'] ?? $targetFileHeaders;
                $combineOldRowMapList = [];

                if (isset($row['old'])) {
                    // 更新前データをカラムをkeyとするmapに変換(ENABLEは削除する)
                    $combineOldRowMapList = array_map(function ($oldRow) use ($oldColumns, $filename) {
                        if (count($oldRow) !== count($oldColumns)) {
                            // ヘッダーのカラム数とレコードのカラム数が一致しない
                            $oldRowJson = json_encode($oldRow);
                            $oldColumnsJson = json_encode($oldColumns);
                            throw new \Exception("{$filename} data unmatched from old, oldRow:[{$oldRowJson}], oldColumns:[{$oldColumnsJson}]");
                        }

                        $combineOldRow = array_combine($oldColumns, $oldRow);
                        unset($combineOldRow['ENABLE']);
                        return $combineOldRow;
                    }, $row['old']);
                }

                // 更新後のデータの処理
                // カラム変更があれば更新後のカラム情報を、なければシートのカラム情報をもつ
                $newColumns = $row['new_columns'] ?? $targetFileHeaders;
                $combineNewRowMapList = [];
                if (isset($row['new'])) {
                    // 更新後データをカラムをkeyとするmapに変換(ENABLEは削除する)
                    $combineNewRowMapList = array_map(function ($newRow) use ($newColumns, $filename) {
                        if (count($newRow) !== count($newColumns)) {
                            // ヘッダーのカラム数とレコードのカラム数が一致しない
                            $newRowJson = json_encode($newRow);
                            $newColumnsJson = json_encode($newColumns);
                            throw new \Exception("{$filename} data unmatched from new, newRow:[{$newRowJson}], newColumns:[{$newColumnsJson}]");
                        }

                        $combineNewRow = array_combine($newColumns, $newRow);
                        unset($combineNewRow['ENABLE']);
                        return $combineNewRow;
                    }, $row['new']);
                }

                // 新規追加したデータを保存する
                foreach ($combineNewRowMapList as $newRow) {
                    $newId = $newRow['id'];
                    $matchOldData = array_values(array_filter($combineOldRowMapList, function ($oldRow) use ($newId) {
                        return $oldRow['id'] === $newId;
                    }));
                    if (!empty($matchOldData)) {
                        // new.idに一致するデータがoldにある場合は新規追加ではないので次のループへ
                        continue;
                    }
                    // oldになかった場合、newのデータは新規追加されたとみなす
                    $fileResult['new'][] = $newRow;

                    // リリースキーごとの新規データ件数を保持する
                    $newReleaseKey = $newRow['release_key'];
                    if (!isset($newRowCountMapByReleaseKey[$newReleaseKey])) {
                        $newRowCountMapByReleaseKey[$newReleaseKey] = 0;
                    }
                    $newRowCountMapByReleaseKey[$newReleaseKey]++;
                }

                // 更新データまたは削除データを保存する
                foreach ($combineOldRowMapList as $oldRow) {
                    $oldId = $oldRow['id'];
                    $oldReleaseKey = $oldRow['release_key'] ?? 0;

                    $matchNewData = array_values(array_filter($combineNewRowMapList, function ($newRow) use ($oldId) {
                        return $newRow['id'] === $oldId;
                    }));
                    if (empty($matchNewData)) {
                        // old.idに一致するデータがnewになかった場合、oldのデータは削除されたとみなす
                        // idを変更した場合でも削除と判定する
                        $fileResult['delete'][] = $oldRow;

                        // リリースキーごとの削除データ件数を保持する
                        if (!isset($deleteRowCountMapByReleaseKey[$oldReleaseKey])) {
                            $deleteRowCountMapByReleaseKey[$oldReleaseKey] = 0;
                        }
                        $deleteRowCountMapByReleaseKey[$oldReleaseKey]++;

                        continue;
                    }
                    // 多次元配列から一次元配列に変換
                    $newRow = array_merge(...$matchNewData);

                    // oldとnewを比較して差分を取得する
                    $modifyColumnMap = array_diff_assoc($newRow, $oldRow);
                    $fileResult['modify'][] = [
                        'beforeRow' => $oldRow,
                        'modifyColumnMap' => $modifyColumnMap,
                    ];

                    // リリースキーごとの変更データ件数を保持する
                    if (!isset($modifyRowCountMapByReleaseKey[$oldReleaseKey])) {
                        $modifyRowCountMapByReleaseKey[$oldReleaseKey] = 0;
                    }
                    $modifyRowCountMapByReleaseKey[$oldReleaseKey]++;
                }

                // テーブルの構造変更情報を生成
                // 削除されたカラムを抽出
                $structureDiffDeleteData = array_diff($oldColumns, $newColumns);
                // 追加されたカラムを抽出
                $structureDiffAddData = array_diff($newColumns, $oldColumns);
            }

            // 表示用のヘッダーを生成
            // 追加カラム、削除カラムどちらも含めて表示する
            $mergedHeaders = array_unique(array_merge($oldColumns, $newColumns));
            // ヘッダーからENABLEを削除
            $mergedHeaders = array_values(array_filter($mergedHeaders, function ($header) {
                return $header !== 'ENABLE';
            }));
            // 左から順にid,release_key,その他となるようにソート
            $fixedOrder = ["id", "release_key"];
            $resultHeaders = $mergedHeaders;
            if (empty(array_diff($fixedOrder, $mergedHeaders))) {
                // idとrelease_keyが存在していたら、配列の最初に順に固定する
                // 残りの要素を後ろに追加した配列を作る
                $remainingElements = array_values(array_diff($mergedHeaders, $fixedOrder));
                $resultHeaders = array_merge($fixedOrder, $remainingElements);
            }

            // filenameからcsv拡張子を削除
            $baseFileName = pathinfo($filename, PATHINFO_FILENAME);

            $entities[] = new ImportDataDiffEntity(
                $baseFileName,
                $fileResult,
                $resultHeaders,
                $structureDiffAddData,
                $structureDiffDeleteData,
                $modifyRowCountMapByReleaseKey,
                $newRowCountMapByReleaseKey,
                $deleteRowCountMapByReleaseKey
            );
        }

        return $entities;
    }
}

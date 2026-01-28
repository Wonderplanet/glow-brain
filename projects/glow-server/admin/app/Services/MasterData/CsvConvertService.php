<?php

namespace App\Services\MasterData;

use App\Constants\Database;
use App\Constants\SpreadSheetLabel;
use App\Entities\MasterData\DatabaseCsvEntity;
use App\Entities\MasterData\RawDatabaseCsvEntity;
use App\Entities\MasterData\ReleaseControlCsvEntity;
use App\Operators\MasterDataDBOperator;
use App\Services\MasterData\ClassSearchService;
use App\Utils\SpreadSheetSerialDate;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CsvConvertService
{
    private MasterDataDBOperator $masterDataDbOperator;
    private ClassSearchService $classSearchService;
    public function __construct(MasterDataDBOperator $masterDataDbOperator = null)
    {
        $this->masterDataDbOperator = $masterDataDbOperator ?: new MasterDataDBOperator();
        $this->classSearchService = new ClassSearchService();
    }

    // ReleaseKey込みのOriginalテーブルデータをデータベースにインポート可能なCSVに変換する
    public function convertCsvEntity(array $data, string $className): RawDatabaseCsvEntity
    {
        $convertedData = $this->convertCommon($data);
        return new RawDatabaseCsvEntity($convertedData, $className);
    }

    // カラムを集約し、JSONのカラムに落とし込む
    public function aggregateJson(RawDatabaseCsvEntity $convertedData, ReleaseControlCsvEntity $releaseControlEntity): array
    {
        // DBで定義されているカラム情報を取得
        $dbName = $this->masterDataDbOperator->getMasterDbName($releaseControlEntity->getAvailableVersions()[0]);
        $this->masterDataDbOperator->setConnection($dbName);

        $tableName = $convertedData->getTableName();
        $columnRecords = $this->masterDataDbOperator->showColumns($tableName);
        $columns = [];
        $types = [];
        $nullables = [];
        $defaults = [];
        foreach ($columnRecords as $column) {
            $columns[] = $column->Field;
            $types[$column->Field] = $column->Type;
            $nullables[$column->Field] = $column->Null == "YES";
            $defaults[$column->Field] = $column->Default;
        }

        $data = $this->convertToDataArray($convertedData->getData());

        $resultData = [];
        $availableReleaseKeys = $releaseControlEntity->getAvailableReleaseKeys();
        foreach ($availableReleaseKeys as $availableReleaseKey) {
            $resultData[$availableReleaseKey] = [];
        }
        $gitRevision = $releaseControlEntity->getGitRevision();

        $struct = $this->classSearchService->getStructModelClassName($tableName); // resourceのキーの型をクライアントに合わせる

        foreach ($data as $d) {
            $row = array();
            // OPTIMIZE: インデックス化する
            foreach (array_keys($d) as $columnName) {
                // テーブルにあるカラムのデータ処理
                if (in_array(Str::snake($columnName), $columns)) {
                    $value = trim(@$d[$columnName]);

                    // DB上boolカラムのtrue/falseは1/0に置換
                    //　スプシ上TRUE/FALSEだと[string]'TRUE'/'FALSE'か[boolean]1/null になる
                    if (str_starts_with($types[$columnName], "tinyint")) {
                        if (strtolower($value) === "true") $value = 1;
                        if (strtolower($value) === "false" || empty($value)) $value = 0;
                    } elseif (str_contains($types[$columnName], "int") || str_starts_with($types[$columnName], "double")) {
                        if (empty($value) && $value !== '0') { // TiDBで数値を空文字にして登録しようとするとエラーになるのでnullか0に置換
                            if ($nullables[$columnName] && empty($defaults[$columnName])) $value = SpreadSheetLabel::NULL_CELL_PLACEHOLDER;
                            else if (empty($defaults[$columnName])) $value = 0;
                            else $value = $defaults[$columnName];
                        }
                    }

                    $row[$columnName] = @$value;
                }
            }

            // 時刻データのカラムがstringのままならCarbonに変換し、JSTをUTCにする
            foreach ($row as $k => &$v) {
                if (isset($types[$k]) && $types[$k] === "datetime" && is_string($v)) {
                    $v = $this->convertToDateTime($v);
                } elseif (isset($types[$k]) && $types[$k] === "timestamp" && is_string($v)) {
                    $v = $this->convertToDateTime($v);
                }
            }
            unset($v);

            // 各リリースキーに登録
            // OPTIMIZE: リリースキーが複数になるとメモリに不安あり
            if (!isset($row["release_key"])) $rowReleaseKey = "0"; // 番兵
            else $rowReleaseKey = $row["release_key"];
            foreach ($availableReleaseKeys as $k) {
                // リリースキー指定がなかった行 or $kよりも未来のリリースキーに指定された行はスキップする
                // TODO: master_constsがrelease_keyを持たないため、release_keyがない場合はスキップしないよう暫定対応
                if (/*$rowReleaseKey !== "0" && */ !in_array($rowReleaseKey, $releaseControlEntity->getExcludeKeys($k))) {
                    // TODO: 同じIDのデータがあればリリースキーの順にupdateする
                    $resultData[$k][] = $row;
                }
            }
        }

        // 最初にヘッダになる配列キーを挿入してエンティティに各データ設定
        $entities = [];
        foreach ($resultData as $releaseKey => $data) {
            if (!isset($data[0])) continue; // データがない場合はスキップ

            array_unshift($data, array_keys($data[0]));

            $entity = new DatabaseCsvEntity($data, $convertedData->getTitle());
            $entity->setReleaseKey($releaseKey);
            $entity->setGitRevision($gitRevision);

            $entities[] = $entity;
        }

        return $entities;
    }

    private function convertCommon(array $csvFormatData): array
    {
        $result = array();
        $activeColumnIndexes = array();


        $phase = 1;
        $enableColumnIndex = 0;
        foreach ($csvFormatData as $row) {
            // フェーズ1: カラムの探索
            if ($phase === 1) {
                // NOTE: 現在1行目に設定, なぜかin_arrayがスルーされるので調査
                // 「ENABLE」が存在するrowまでスキップ
                // if (!in_array(SpreadSheetLabel::COL_NAME_IDENTIFIER, $row)) {
                //     continue;
                // }

                // カラム名の取得
                foreach ($row as $i => $column) {
                    if ($column === "" || str_starts_with($column, "#")) {
                        continue;
                    }
                    if ($column === SpreadSheetLabel::COL_NAME_IDENTIFIER) {
                        $enableColumnIndex = $i;
                        continue;
                    }
                    $activeColumnIndexes[] = $i;
                }
                $result[] = array_map(fn($idx) => @$row[$idx], $activeColumnIndexes);
                $phase = 2;
            }

            // フェーズ2: データ捜査
            if ($phase === 2) {
                // TODO: タグ取り込みの仕組み実装
                if (isset($row[$enableColumnIndex]) && $row[$enableColumnIndex] == SpreadSheetLabel::ENABLE_ROW_IDENTIFIER) {
                    $result[] = array_map(fn($idx) => $this->convertText(@$row[$idx]), $activeColumnIndexes);
                }
            }
        }
        if (count($activeColumnIndexes) == 0)
        {
            // CSVのフォーマットが適切でない場合は空配列をかえす
            // 呼び出し側でハンドリングしてスキップなりなんなりする
            return [];
        }
        return $result;
    }

    // マスターデータのスプレッドシートで取得したテキストをそのまま使わずに前処理を掛ける
    private function convertText(?string $str): ?string
    {
        if ($str === null) return null;
        if ($str === "TRUE" || $str === "FALSE") return strtolower($str);
        return $str;
    }

    /**
     * 連想配列に変換して処理しやすくする
     *
     * @param array<mixed> $csv
     * @return array<mixed>
     */
    public function convertToDataArray(array $csv): array
    {
        return $this->toArrayInputPlaceholder($csv);
    }

    /**
     * 連想配列に変換して処理しやすくする(placeholderでnullを置換している)
     *
     * @param array<mixed> $csv
     * @return array<mixed>
     */
    public function convertToDataArrayUsePlaceholder(array $csv): array
    {
        return $this->toArrayInputPlaceholder($csv, SpreadSheetLabel::NULL_CELL_PLACEHOLDER);
    }

    /**
     * 連想配列に変換する
     * @param array $csv
     * @param string|null $placeholder
     * @return array
     */
    private function toArrayInputPlaceholder(array $csv, ?string $placeholder = null): array
    {
        $columns = [];
        $result = [];
        foreach ($csv as $i => $rows) {
            if ($i === 0)
            {
                $columns = $rows;
                continue;
            }
            $r = [];
            foreach ($columns as $j => $column) {
                if (!isset($rows[$j]) || $rows[$j] === $placeholder) {
                    // 未定義カラムや特定文字列はnullにする
                    $r[Str::snake($column)] = null;
                } else {
                    $r[Str::snake($column)] = @$rows[$j];
                }
            }
            $result[] = $r;
        }
        return $result;
    }

    public function convertToDateTime(string $value, string $format = SpreadSheetLabel::DATETIME_FORMAT_DATABASE): string
    {
        // 入力はJSTで、出力はUTC
        $utcTimeZone = new DateTimeZone('UTC');
        // テキストでもフォーマットがバラバラなのでCarbonでパースする
        if (is_numeric($value)) {
            $dateTime = SpreadSheetSerialDate::convertSerialDateToDateTime($value);
        } else {
            try {
                $dateTime = Carbon::createFromFormat($format, $value, SpreadSheetLabel::DATETIME_INPUT_TIMEZONE);
            } catch (\Exception $e) {
                return $value;
            }
        }

        if (isset($dateTime)) {
            $value = $dateTime->setTimezone($utcTimeZone)->format($format);
        }
        return $value;
    }
}

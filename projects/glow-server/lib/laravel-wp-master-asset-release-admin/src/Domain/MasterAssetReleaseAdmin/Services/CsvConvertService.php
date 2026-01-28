<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Str;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\DatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\MngMasterReleaseKeyEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\RawDatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\SpreadSheetSerialDate;

/**
 * マスターデータインポートv2管理ツール用
 */
class CsvConvertService
{
    private MasterDataDBOperator $masterDataDbOperator;

    public function __construct(MasterDataDBOperator $masterDataDbOperator = null)
    {
        $this->masterDataDbOperator = $masterDataDbOperator ?: new MasterDataDBOperator();
    }

    /**
     * ReleaseKey込みのOriginalテーブルデータをデータベースにインポート可能なCSVに変換する
     *
     * @param array $data
     * @param string $className
     * @return RawDatabaseCsvEntity
     */
    public function convertCsvEntity(array $data, string $className): RawDatabaseCsvEntity
    {
        $convertedData = $this->convertCommon($data);
        return new RawDatabaseCsvEntity($convertedData, $className);
    }

    /**
     * カラムを集約し、JSONのカラムに落とし込む
     *
     * @param RawDatabaseCsvEntity $convertedData
     * @param MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity
     * @return array
     */
    public function aggregateJson(
        RawDatabaseCsvEntity $convertedData,
        MngMasterReleaseKeyEntity $mngMasterReleaseKeyEntity,
    ): array {
        // DBで定義されているカラム情報を取得
        [
            'releaseKey' => $releaseKey,
            'serverDbHashMap' => $serverDbHashMap,
        ] = $mngMasterReleaseKeyEntity->getMasterDbNameParameter();
        $dbName = $this->masterDataDbOperator->getMasterDbName($releaseKey, $serverDbHashMap);

        if (!$this->masterDataDbOperator->isExist($dbName)) {
            // release_key+server_db_hashのDBがなければデフォルトのDB名をセットする
            $dbName = config('app.env');
        }
        $this->masterDataDbOperator->setConnection($dbName);

        $tableName = $convertedData->getTableName();
        // カラム情報はデフォルトのMstから取得する
        // スプレッドシートのデータはマイグレーション後の想定だが、各リリースマスタはマイグレーション前の状態が維持されているため
        // 最新のマイグレーションが適用された環境から取得する必要がある
        $columnRecords = $this->masterDataDbOperator->showColumnsOnDefaultMst($tableName);
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
        $availableReleaseKeys = $mngMasterReleaseKeyEntity->getReleaseKeys();
        foreach ($availableReleaseKeys as $availableReleaseKey) {
            $resultData[$availableReleaseKey] = [];
        }

        foreach ($data as $d) {
            $row = array();
            // OPTIMIZE: インデックス化する
            foreach (array_keys($d) as $columnName) {
                // テーブルにあるカラムのデータ処理
                if (in_array(Str::snake($columnName), $columns)) {
                    $value = trim(@$d[$columnName]);

                    // DB上boolカラムのtrue/falseは1/0に置換
                    // スプシ上TRUE/FALSEだと[string]'TRUE'/'FALSE'か[boolean]1/null になる
                    if (str_starts_with($types[$columnName], "tinyint")) {
                        if (strtolower($value) === "true") $value = 1;
                        if (strtolower($value) === "false" || empty($value)) $value = 0;
                    } elseif (str_contains($types[$columnName], "int") || str_starts_with($types[$columnName], "double")) {
                        if (empty($value) && $value !== '0') { // TiDBで数値を空文字にして登録しようとするとエラーになるのでnullか0に置換
                            if ($nullables[$columnName] && empty($defaults[$columnName])) $value = SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER;
                            else if (empty($defaults[$columnName])) $value = 0;
                            else $value = $defaults[$columnName];
                        }
                    }

                    $row[$columnName] = @$value;
                }
            }

            // 時刻データのカラムがstringのままならCarbonに変換し、JSTをUTCにする
            $checkDateColumnTypes = ["datetime", "timestamp"];
            foreach ($row as $k => &$v) {
                if ($this->isSkipConvertToDateTime($v)) {
                    // 値がnullとして扱う値なら次のループへ
                    continue;
                }

                if (isset($types[$k]) && in_array($types[$k], $checkDateColumnTypes, true) && is_string($v)) {
                    $v = $this->convertToDateTime($v);
                }
            }
            unset($v);

            // 各リリースキーに登録
            // OPTIMIZE: リリースキーが複数になるとメモリに不安あり
            // git管理しているmasterdata上でrelease_keyが設定されてなければ全リリースキーで有効なデータとなるように0を設定
            $rowReleaseKey = isset($row["release_key"]) ? (int) $row["release_key"] : 0;
            foreach ($availableReleaseKeys as $availableReleaseKey) {
                if ($rowReleaseKey <= $availableReleaseKey) {
                    // データに設定されたリリースキーが有効であればデータを更新する
                    $resultData[$availableReleaseKey][] = $row;
                }
            }
        }

        // 最初にヘッダになる配列キーを挿入してエンティティに各データ設定
        $entities = [];
        foreach ($resultData as $releaseKey => $data) {
            if (!isset($data[0])) {
                // データがない場合は空のエンティティを作成
                $entity = new DatabaseCsvEntity([], $convertedData->getTitle());
                $entity->setReleaseKey($releaseKey);
                $entities[] = $entity;
                continue;
            }

            array_unshift($data, array_keys($data[0]));

            $entity = new DatabaseCsvEntity($data, $convertedData->getTitle());
            $entity->setReleaseKey($releaseKey);

            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * 対象のカラムデータをnullとするかチェック
     *
     * @param string|null $columnValue
     * @return bool (false:nullではない、true:nullとして扱う)
     */
    private function isSkipConvertToDateTime(?string $columnValue): bool
    {
        // nullとして扱うパラメータを定義
        $checks = [
            '',
            SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER,
            strtolower(SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER), // 小文字に変換
            SpreadSheetLabel::NULL_CELL_PLACEHOLDER,
            strtolower(SpreadSheetLabel::NULL_CELL_PLACEHOLDER), // 小文字に変換
        ];
        return is_null($columnValue) || in_array($columnValue, $checks, true);
    }

    /**
     * @param array $csvFormatData
     * @return array
     */
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

    /**
     * マスターデータのスプレッドシートで取得したテキストをそのまま使わずに前処理を掛ける
     *
     * @param string|null $str
     * @return string|null
     */
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
    private function convertToDataArray(array $csv): array
    {
        return $this->toArrayInputPlaceholder($csv, [null]);
    }

    /**
     * 連想配列に変換して処理しやすくする(placeholderでnullを置換している)
     *
     * @param array<mixed> $csv
     * @return array<mixed>
     */
    public function convertToDataArrayUsePlaceholder(array $csv): array
    {
        return $this->toArrayInputPlaceholder(
            $csv,
            [
                SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER,
                strtolower(SpreadSheetLabel::UNDER_BAR_NULL_CELL_PLACEHOLDER), // 小文字に変換
                SpreadSheetLabel::NULL_CELL_PLACEHOLDER,
                strtolower(SpreadSheetLabel::NULL_CELL_PLACEHOLDER), // 小文字に変換
            ]
        );
    }

    /**
     * 連想配列に変換する
     * @param array $csv
     * @param array<int, string | null> $placeholders
     * @return array
     */
    private function toArrayInputPlaceholder(array $csv, array $placeholders): array
    {
        $columns = [];
        $result = [];
        foreach ($csv as $i => $rows) {
            if ($i === 0)
            {
                // 要素0はカラム行の想定なのでスキップ
                $columns = $rows;
                continue;
            }
            $r = [];
            foreach ($columns as $j => $column) {
                if (!isset($rows[$j]) || in_array($rows[$j], $placeholders, true)) {
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

    /**
     * @param string $value
     * @param string $format
     * @return string
     */
    public function convertToDateTime(string $value, string $format = SpreadSheetLabel::DATETIME_FORMAT_DATABASE): string
    {
        // 入力はJSTで、出力はUTC
        $utcTimeZone = new DateTimeZone('UTC');
        // テキストでもフォーマットがバラバラなのでCarbonでパースする
        if (is_numeric($value)) {
            $dateTime = SpreadSheetSerialDate::convertSerialDateToDateTime($value);
        } else {
            $dateTime = new Carbon($value, SpreadSheetLabel::DATETIME_INPUT_TIMEZONE);
        }

        if (isset($dateTime)) {
            // GLOWでは設定でタイムゾーン変換指定しているのでタイムゾーン指定を外す
            $value = $dateTime->format($format);
        }
        return $value;
    }
}

<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities;

use Google\Service\Sheets\Sheet;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;

/**
 * マスタデータインポートv2用
 * スプレッドシート1タブ分の情報をもつEntityクラス
 *
 * MEMO v1と実装が変わる可能性を考慮し別クラスで作成
 */
class SpreadSheetCsvEntity
{
    private array $data;
    private bool $hasI18nData;
    private Sheet $sheet;
    private string $fileId;
    private string $fileName;
    private string $title;
    private string $url;

    public function __construct(string $fileId, string $fileName, Sheet $sheet, array $data)
    {
        $this->hasI18nData = false;
        $this->sheet = $sheet;
        $this->title = $sheet->getProperties()->getTitle();
        $this->fileId = $fileId;
        $this->fileName = $fileName;
        $this->url = "https://docs.google.com/spreadsheets/d/{$fileId}/edit#gid={$this->getSheetId()}";

        // デフォルトのリリースキーは0
        $this->setData($data, 0);
    }

    /**
     * @return bool
     */
    public function existI18nConvert(): bool
    {
        return $this->hasI18nData;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * シート上のテーブルデータからメモ欄を省いて初期化
     * 渡されたdataのうち、指定したreleaseKey以下の情報だけを保持する
     *
     * @param array $data
     * @param int $releaseKey
     * @return void
     */
    public function setData(array $data, int $releaseKey): void
    {
        if (empty($data)) {
            $this->data = [];
            return;
        }

        $tableKeyIdx = 0;
        foreach ($data as $row) {
            if ($row[0] === SpreadSheetLabel::TABLE_NAME_IDENTIFIER && count(array_unique(array_slice($row, 1))) === 1) {
                // $row[1]以降の値がすべて同一の場合は単体テーブル取り込み
                $this->hasI18nData = false;
            } else {
                foreach ($row as $cell) {
                    if (strpos($cell, 'I18n') !== false) {
                        // i18n情報あり
                        $this->hasI18nData = true;
                        break;
                    }
                }
            }

            if ($this->hasI18nData == false &&
                (empty($row) || $row[0] != SpreadSheetLabel::COL_NAME_IDENTIFIER)
            ) {
                // ENABLEが1列目にないためメモ欄またはレコードデータでない行データ
                $tableKeyIdx++;
                continue;
            }
            break;
        }

        if ($tableKeyIdx !== 0) {
            // メモ行はここで削除
            // i18nの情報を含んでいなければ、テーブル行もここで削除する
            array_splice($data, 0, $tableKeyIdx);
        }

        // スプシデータの除外処理
        // 1.左端のセル(ENABLE列)が空になっている行は$this->dataに含めないようにする
        // 2.スプシ内にデータとは関係ないメモ列が存在する場合は含めないようにする
        // 3.自環境の最新のリリースキーよりも大きいリリースキーが登録されている場合は除外する
        // MEMO
        // メモ列の除外を行うと配列のkeyが歯抜けの状態で生成されるが
        // convertToJSONメソッドでkeyを見て処理をする箇所があるので、歯抜けは維持した状態で生成する必要がある
        $newData = [];
        $emptyColumnKeys = []; // カラム行で空白が存在したキーの配列
        $columnSize = 0;
        $releaseKeyColumnKeyNumber = null; // 対象シートでrelease_keyが何列目にあるかを格納する
        foreach ($data as $row) {
            if (empty($row)) {
                continue;
            }
            $leftCell = (string)$row[0];

            // $rowに `releaseKey` カラムが存在する場合、何列目かを取得する(カラム列でない場合はfalse)
            $releaseKeyColumnKey = array_search(SpreadSheetLabel::RELEASE_KEY_COLUMN, $row, true);
            if ($releaseKeyColumnKey !== false) {
                $releaseKeyColumnKeyNumber = $releaseKeyColumnKey;
            }

            // 有効なレコードでなければスキップする
            if (!in_array($leftCell, [SpreadSheetLabel::TABLE_NAME_IDENTIFIER, SpreadSheetLabel::COL_NAME_IDENTIFIER], true)) {
                // セルの行がレコード行である(セルの値が`TABLE`または`ENABLE`ではない)
                if ($leftCell !== SpreadSheetLabel::ENABLE_ROW_IDENTIFIER) {
                    // かつ、`e`以外の場合はスキップ(有効なレコードではない)
                    continue;
                }

                $rowReleaseKeyValue = (int) $row[$releaseKeyColumnKeyNumber];
                if (!is_null($releaseKeyColumnKeyNumber)
                    && $releaseKey !== 0
                    && $rowReleaseKeyValue > $releaseKey
                ) {
                    // または、$releaseKeyColumnKeyNumber がnullでなく、$releaseKeyが0以外で、シートに記載されているreleaseKeyが自環境のリリースキーよりも大きい場合
                    // スキップ(有効なレコードではない)
                    continue;
                }
            }

            if (in_array($leftCell, [SpreadSheetLabel::TABLE_NAME_IDENTIFIER, SpreadSheetLabel::COL_NAME_IDENTIFIER], true)) {
                // `TABLE`または`ENABLE`から始まる行から、メモ列判別のため下記を取得する
                $emptyColumnKeys = array_keys($row, "", true); // カラム行の中で空文字があるキーを取得

                // 空文字を除外する
                $filteredRow = array_filter($row, function ($value) {
                    return $value !== "";
                });
                $columnSize = count($filteredRow); // カラム行のデータ(カラム)数
                $newData[] = $filteredRow;
                continue;
            }

            foreach ($emptyColumnKeys as $key) {
                // カラム行の空文字列に該当するkeyをレコードから削除
                unset($row[$key]);
            }

            if (count($row) > $columnSize) {
                // カラム列数が1レコードの列数より少ない場合、$rowの末尾にメモ列が追加されているので、メモ列を削除する
                $deleteIdxSize = count($row) - $columnSize;
                // 配列の末尾から指定分削除する
                for ($i = 0; $i < $deleteIdxSize; $i++) {
                    $lastKey = array_key_last($row);
                    unset($row[$lastKey]);
                }
            } elseif (count($row) < $columnSize) {
                // columnSizとcount($row)の差の分だけ$rowの末尾に空文字を追加する
                $addIdxSize = $columnSize - count($row);
                for ($i = 0; $i < $addIdxSize; $i++) {
                    $row[] = "";
                }
            }

            $newData[] = $row;
        }
        $this->data = $newData;
    }

    /**
     * @param array $data
     * @return void
     */
    public function mergeData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $name
     * @return void
     */
    private function setTitle(string $name): void
    {
        $this->title = $name;
    }

    /**
     * @return string
     */
    public function getFileId(): string
    {
        return $this->fileId;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return int
     */
    public function getSheetId(): int
    {
        return $this->sheet->getProperties()->getSheetId();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * i18n要素を抽出して、通常マスタとi18nマスタのSpreadSheetCsvEntityを生成して返す
     *
     * @return SpreadSheetCsvEntity[]
     * @throws \Exception
     */
    public function createI18nIncludingSheets(): array
    {
        $entities = [];
        if (!$this->existI18nConvert()) return $entities;

        // 通常テーブルとi18nテーブルを分けるためにテーブル別Jsonを生成
        $tableData = $this->convertToJSON($this->data);
        // 共通データ抽出
        $enableData = array_merge([SpreadSheetLabel::COL_NAME_IDENTIFIER], $tableData[SpreadSheetLabel::COL_NAME_IDENTIFIER]);
        unset($tableData[SpreadSheetLabel::COL_NAME_IDENTIFIER]);

        // 通常テーブルとi18nテーブルのシートデータを生成
        foreach ($tableData as $key => $value) {
            $sheetData = [];
            // i18nデータ抽出
            if (strpos($key, 'I18n') !== false) {
                $sheetData = $this->convertToI18nSheetData($value, $enableData);
            }
            // i18n以外の通常データ抽出
            else {
                $sheetData = $this->convertToSheetData($value, $enableData);
            }

            // entity生成
            $entity = new SpreadSheetCsvEntity($this->fileId, $this->fileName, $this->sheet, $sheetData);
            $entity->setTitle($key);
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * 行ごとのテーブルデータからヘッダー要素とレコード要素を仕分けしてJson形式に変換
     *
     * @param array $sheetData
     * @return array
     * @throws \Exception
     */
    private function convertToJSON(array $sheetData): array
    {
        if (!isset($sheetData[0])) {
            // $sheetData[0]はシート上の テーブル(`TABLE`から始まる)行がある想定だが、存在しなかった
            throw new \Exception('not found table name row from sheetData', [$sheetData]);
        }

        if (!isset($sheetData[1])) {
            // $sheetData[1]はシート上の カラム(`ENABLE`から始まる)行がある想定だが、存在しなかった
            throw new \Exception('not found column name row from sheetData', [$sheetData]);
        }

        $result = [];
        $tableNames = $sheetData[0];
        $columnNames = $sheetData[1];
        // カラム名とレコード行の間にヘッダーがある想定
        $records = array_slice($sheetData, 2);
        foreach ($columnNames as $idx => $columnName) {
            $table = $tableNames[$idx];

            // `COL_NAME_IDENTIFIER`対応
            if ($table == SpreadSheetLabel::TABLE_NAME_IDENTIFIER && $columnName == SpreadSheetLabel::COL_NAME_IDENTIFIER) {
                $result[SpreadSheetLabel::COL_NAME_IDENTIFIER] = array_column($records, 0);
                continue;
            }

            if (strpos($columnName, '.') !== false) {
                // i18nテーブル
                list($field, $lang) = explode('.', $columnName);
                $result[$table][$lang]['release_key'] = array_column($records, array_search('release_key', $columnNames));
                $result[$table][$lang]['id'] = array_column($records, array_search('id', $columnNames));
                $result[$table][$lang]['language'] = array_fill(0, count($records), $lang);
                foreach ($records as $_ => $record) {
                    $result[$table][$lang][$field][] = $record[$idx] ?? '';
                }
            }
            else {
                // 通常テーブル
                $result[$table][$columnName] = array_column($records, $idx);
            }
        }
        return $result;
    }

    /**
     * Jsonデータをシートデータに変換
     *
     * @param array $jsonData
     * @param array $enableData
     * @return array
     */
    private function convertToSheetData(array $jsonData, $enableData): array
    {
        $sheetData = [];
        // ヘッダー準備
        $columns = array_keys($jsonData);
        $sheetData[] = $columns;
        array_unshift($sheetData[0], $enableData[0]);
        // レコード数を取得
        $rowCount = count($jsonData[$columns[0]]);
        for ($i = 0; $i < $rowCount; $i++) {
            $row = [];
            // 行データ準備
            foreach ($columns as $column) {
                $row[] = $jsonData[$column][$i];
            }
            // 先頭に共通データセット
            array_unshift($row, $enableData[$i+1]);
            // 行データセット
            $sheetData[] = $row;
        }
        return $sheetData;
    }

    /**
     * i18n要素のJsonデータをシートデータに変換
     *
     * @param array $jsonData
     * @param array $enableData
     * @return array
     */
    private function convertToI18nSheetData(array $jsonData, array $enableData): array
    {
        $sheetData = [];
        $columns = array_keys(current($jsonData));
        $idIndex = array_search('id', $columns);
        // 親テーブルIDの列をIDの隣に追加
        $parentIdName = $this->pascalToSnake($this->title) . '_id';
        array_splice($columns, $idIndex + 1, 0, $parentIdName);
        // ヘッダー準備
        $sheetData[] = $columns;
        array_unshift($sheetData[0], $enableData[0]);
        foreach ($jsonData as $lang => $fields) {
            $rowCount = count($fields[$columns[$idIndex]]);
            for ($i = 0; $i < $rowCount; $i++) {
                $row = [];
                // 行データ準備
                foreach ($columns as $column) {
                    if ($column == $parentIdName) continue;
                    if ($column === 'id') {
                        // 子テーブルIDは「親テーブルID」+「_（アンダーバー）」+「言語」のフォーマットに統一
                        $row[] = $fields['id'][$i] . '_' . $lang;
                        // 親テーブルID
                        $row[] = $fields['id'][$i];
                    }
                    else {
                        $row[] = $fields[$column][$i] ?? '';
                    }
                }
                // 先頭に共通データセット
                array_unshift($row, $enableData[$i+1]);
                // 行データセット
                $sheetData[] = $row;
            }
        }
        return $sheetData;
    }

    /**
     * パスカルケースからスネークケースへ変換
     *
     * @param $string
     * @return string
     */
    private function pascalToSnake($string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', lcfirst($string)));
    }
}

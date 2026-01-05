<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Filament\Notifications\Notification;
use WonderPlanet\Domain\Admin\Operators\CacheOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetRequestEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\CSVOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GoogleDriveOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GoogleSpreadSheetOperator;

/**
 * マスターデータインポートv2用
 * googleスプレッドシートを扱うためのサービスクラス
 *
 * MEMO v1と実装が変わる可能性を考慮し別クラスで作成
 */
class SpreadSheetFetchService
{
    // TODO v1ツールのコード(\App\Services\MasterData\SpreadSheetFetchService)がオミットされたらV2の記述を削除していい
    private const SHEETS_CACHE_KEY_V2 = "SheetsForSpreadSheetV2";
    private const RAW_DATA_CACHE_KEY_PREFIX = "SheetRawData";

    private CacheOperator $cacheOperator;
    private CSVOperator $csvOperator;
    private GoogleDriveOperator $driveOperator;
    private GoogleSpreadSheetOperator $sheetOperator;

    private string $configKeySpreadSheetCsvDir = 'wp_master_asset_release_admin.spreadSheetCsvDir';

    public function __construct()
    {
        $this->cacheOperator = new CacheOperator();
        $this->csvOperator = new CSVOperator();
        $googleCredentialPath = config('wp_master_asset_release_admin.googleCredentialPath');
        $this->driveOperator = new GoogleDriveOperator($googleCredentialPath);
        $this->sheetOperator = new GoogleSpreadSheetOperator($googleCredentialPath);
    }

    /**
     * APCuにキャッシュしているスプレッドシート情報を取得する
     *
     * @return array
     */
    public function getSheetsCache(): array
    {
        $sheets = $this->cacheOperator->getApcCache(self::SHEETS_CACHE_KEY_V2);
        return is_null($sheets) ? [] : $sheets;
    }

    /**
     * APCuにキャッシュしているスプレッドシート情報を削除する
     *
     * @return void
     */
    public function deleteSheetsCache(): void
    {
        $this->cacheOperator->deleteApcCache(self::SHEETS_CACHE_KEY_V2);
    }

    /**
     * 生データを APCu にキャッシュ
     *
     * @param int $sheetId
     * @param array $rawData
     * @return void
     */
    private function setRawDataCache(int $sheetId, array $rawData): void
    {
        $cacheKey = self::RAW_DATA_CACHE_KEY_PREFIX . "_{$sheetId}";
        $this->cacheOperator->saveApcCache($cacheKey, $rawData);
    }

    /**
     * APCu から生データを取得（public メソッド）
     *
     * @param int $sheetId
     * @return array|null
     */
    public function getRawDataCache(int $sheetId): ?array
    {
        $cacheKey = self::RAW_DATA_CACHE_KEY_PREFIX . "_{$sheetId}";
        return $this->cacheOperator->getApcCache($cacheKey);
    }

    /**
     * スプレッドシートのデータを取得してCSVとして保存する
     * チェックしたスプレッドシートのマスター名の配列を返す
     *
     * @param int $releaseKey 指定リリースキー。このリリースキーより大きいリリースキーは登録しない
     * @param SpreadSheetRequestEntity[] $target 取得対象のスプレッドシート、空なら全部
     * @return array<string>
     */
    public function getAndWriteSpreadSheetCsv(int $releaseKey, array $target = []): array
    {
        // シート取得
        \Log::info('getAndWriteSpreadSheetCsv start');
        $spreadSheets = $this->fetch($releaseKey, $target);

        // CSV書き出し
        $titles = [];
        foreach ($spreadSheets as $entity) {
            $csvFilePath = config($this->configKeySpreadSheetCsvDir) . "/" . $entity->getTitle() . ".csv";
            \Log::info('getAndWriteSpreadSheetCsv write csv ' . $csvFilePath);
            $this->csvOperator->write($csvFilePath, $entity->getData());
            $titles[] = $entity->getTitle();
        }

        return $titles;
    }

    /**
     * スプレッドシートをデータ付きでを取得してSpreadSheetCsvEntityとして返す
     * @param int $releaseKey 指定リリースキー。このリリースキーより大きいリリースキーは登録しない
     * @param SpreadSheetRequestEntity[] $target 取得対象のスプレッドシート、空なら全部
     * @return SpreadSheetCsvEntity[]
     */
    public function fetch(int $releaseKey, array $target = []): array
    {
        $sheets = $this->fetchSheets($target);
        return $this->fetchValues($releaseKey, $target, $sheets);
    }

    /**
     * スプレッドシート一覧を取得する
     * キャッシュに情報があればそちらを取得する
     *
     * @return array|SpreadSheetCsvEntity[]
     * @throws \Google\Service\Exception
     */
    public function getSpreadSheetList(): array
    {
        // キャッシュがあるか確認
        $fetchData = $this->getSheetsCache();
        if (empty($fetchData)) {
            // キャッシュにデータがなければ取得しにいく
            $fetchData = $this->fetchSheets();
        }

        return $fetchData;
    }

    /**
     * スプレッドシート一覧を返す
     *
     * @param SpreadSheetRequestEntity[] $target 取得対象のスプレッドシート、空なら全部
     * @return SpreadSheetCsvEntity[] データは空
     * @throws \Google\Service\Exception
     */
    public function fetchSheets(array $target = []): array
    {
        // シート取得
        if (empty($target)) {
            $files = $this->driveOperator->getFileList();
        } else {
            $targetColl = collect($target);
            $files = $targetColl
                ->unique(fn(SpreadSheetRequestEntity $entity) => $entity->getFileId())
                ->map(fn(SpreadSheetRequestEntity $entity) => [
                    'id' => $entity->getFileId(),
                    'fileName' => $entity->getFileName(),
                ])
                ->toArray();
        }

        $sheets = [];
        foreach ($files as $fileData) {
            $fileId = $fileData['id'];
            $fileName = $fileData['fileName'];
            $result = $this->sheetOperator->getSheets($fileId);
            /** @var \Google\Service\Sheets\Sheet $sheet */
            foreach ($result as $sheet) {
                $sheets[] = new SpreadSheetCsvEntity($fileId, $fileName, $sheet, []);
            }
        }

        // `/admin/opr-master-releases/import-from-spread-sheet`画面上リストキャッシュ制御
        if (empty($target)) {
            $this->saveSheetsCache($sheets);
        }

        return $sheets;
    }

    /**
     * スプレッドシートのデータを取得してSpreadSheetCsvEntityとして返す
     *
     * @param int $releaseKey 指定リリースキー。このリリースキーより大きいリリースキーは登録しない
     * @param SpreadSheetRequestEntity[] $target
     * @param SpreadSheetCsvEntity[] $sheets
     * @return SpreadSheetCsvEntity[]
     * @throws \Google\Service\Exception
     */
    public function fetchValues(int $releaseKey, array $target = [], array $sheets = []): array
    {
        if (empty($sheets)) {
            $sheets = $this->fetchSheets();
        }

        $spreadSheets = [];
        foreach ($sheets as $sheet) {
            if (!empty($target)) {
                // $target(チェックした対象のスプシ情報)が存在する場合は$sheetと一致するものを取得する
                $filteredTarget = array_filter($target, function (SpreadSheetRequestEntity $entity) use ($sheet) {
                    return $entity->getFileId() === $sheet->getFileId() && $entity->getSheetId() === (string)$sheet->getSheetId();
                });
                if (empty($filteredTarget)) {
                    // $targetになければ取得しない
                    continue;
                }
            }

            // 対象シートの情報を取得
            $value = $this->sheetOperator->getSheetValues($sheet->getFileId(), $sheet->getTitle());

            // シート情報をバリデーション
            $this->validateSpreadSheetValue($sheet->getTitle(), $value);

            // 生データを APCu にキャッシュ
            $this->setRawDataCache($sheet->getSheetId(), $value);

            // タイトル名にアンダーバーがある場合は
            // アンダーバーまでをタイトル名とし、一つにまとめて使用する
            $pos = strrpos($sheet->getTitle(), '_');
            if ($pos === false) {
                $sheet->setData($value, $releaseKey);
                if ($sheet->existI18nConvert()) {
                    // i18nテーブルデータ生成して２テーブル分を追加
                    $entities = $sheet->createI18nIncludingSheets();
                    foreach ($entities as $entity) {
                        $spreadSheets[$entity->getTitle()] = $entity;
                    }
                } else {
                    $spreadSheets[$sheet->getTitle()] = $sheet;
                }
            } else {
                $title = substr($sheet->getTitle(), 0, $pos);
                $spreadSheets[$title]->mergeData($value);
            }
        }
        return $spreadSheets;
    }

    /**
     * スプレッドシート情報をAPCuにキャッシュする
     *
     * @param array $sheets
     * @return void
     */
    private function saveSheetsCache(array $sheets): void
    {
        $this->deleteSheetsCache();
        $this->cacheOperator->saveForeverApcCache(self::SHEETS_CACHE_KEY_V2, $sheets);
    }

    /**
     * $sheetValue(読み込んだマスターデータスプレッドシートの中身)が問題ないかチェックする
     * バリデーション内容
     *  1.左端がTABLEで始まる行が存在するか
     *  2.左端がENABLEで始まる行が存在するか
     *  3.テーブル行またはENABLE行に空欄が存在する場合、どちらも同じ位置に存在するか(メモ列のチェック)
     *
     * @param string $title
     * @param array $sheetValue
     * @return void
     * @throws \Exception
     */
    private function validateSpreadSheetValue(string $title, array $sheetValue): void
    {
        $isFailedByTableRow = true;
        $isFailedByEnableColumn = true;
        $isFailedByEmptyColumn = true;

        $emptyTableRowColumnKeys = null;
        $emptyEnableRowColumnKeys = null;

        // スプシ情報を1行ずつチェックし、全フラグがtrue(異常なし)になるまで全行チェックする
        foreach ($sheetValue as $row) {
            if (empty($row)) {
                // メモ欄上などの空欄行は許容する為スキップ
                continue;
            }
            if ($row[0] === SpreadSheetLabel::TABLE_NAME_IDENTIFIER) {
                // 左端列に`TABLE`が記載されている行が存在するか
                $isFailedByTableRow = false;

                // TABLE行の中で空文字があるキーを取得
                $emptyTableRowColumnKeys = array_keys($row, "", true);
            }
            if ($row[0] === SpreadSheetLabel::COL_NAME_IDENTIFIER) {
                // 左端列に`ENABLE`が記載されている行が存在するか
                $isFailedByEnableColumn = false;

                // ENABLE(カラム)行の中で空文字があるキーを取得
                $emptyEnableRowColumnKeys = array_keys($row, "", true);
            }
            if (!$isFailedByTableRow && !$isFailedByEnableColumn) {
                // 全てのチェックがfalseなら終了する
                break;
            }
        }

        // テーブル行とENABLE行にメモ列が存在する場合同じ位置に存在するか
        if (is_array($emptyTableRowColumnKeys) && is_array($emptyEnableRowColumnKeys)) {
            if ($emptyTableRowColumnKeys === $emptyEnableRowColumnKeys) {
                // 配列の中身が一致していれば正常
                $isFailedByEmptyColumn = false;
            }
        } else {
            // どちらかが配列になってない場合はテーブル行かENABLE行のエラーが発生してるので、$isFailedByEmptyColumnはエラーにしない
            $isFailedByEmptyColumn = false;
        }

        if ($isFailedByTableRow || $isFailedByEnableColumn || $isFailedByEmptyColumn) {
            // エラー内容を通知して例外を投げる
            $errorMsg = $isFailedByTableRow ? "テーブル行が見つかりませんでした<br/>" : '';
            $errorMsg .= $isFailedByEnableColumn ? "ENABLEが見つかりませんでした<br/>" : '';
            $errorMsg .= $isFailedByEmptyColumn ? "テーブル行またはENABLE行にあるメモ列は空欄である必要があります<br/>" : '';

            Notification::make()
                ->title("{$title} シートの値が不正です")
                ->body($errorMsg)
                ->danger()
                ->color('danger')
                ->persistent() // 通知ポップアップの閉じるボタンを押すまで常に表示させる
                ->send();

            $isFailedByTableRowStr = $isFailedByTableRow ? 'true' : 'false';
            $isFailedByEnableColumnStr = $isFailedByEnableColumn ? 'true' : 'false';
            $isFailedByEmptyColumnStr = $isFailedByEmptyColumn ? 'true' : 'false';
            $logMsg = 'SpreadSheetFetchService バリデーションエラー';
            $logMsg .= " title:{$title},";
            $logMsg .= "isFailedByTableRow:{$isFailedByTableRowStr},";
            $logMsg .= "isFailedByEnableColumn:{$isFailedByEnableColumnStr},";
            $logMsg .= "isFailedByEmptyColumn:{$isFailedByEmptyColumnStr}";

            throw new \Exception($logMsg);
        }
    }
}

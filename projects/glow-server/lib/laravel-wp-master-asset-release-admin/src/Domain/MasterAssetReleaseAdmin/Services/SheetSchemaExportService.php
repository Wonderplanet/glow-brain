<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\SpreadSheetLabel;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\SpreadSheetRequestEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\CSVOperator;

/**
 * スプレッドシートのヘッダー情報（memo行、TABLE行、ENABLE行）をCSVとして出力するサービス
 *
 * マスタデータの自動生成のために利用する目的で作成。
 * スプレッドシートはプランナーさんたちが自由に列を追加・削除・変更できるため、それらのヘッダー情報を取得し、テンプレートファイルとして利用するため。
 */
class SheetSchemaExportService
{
    private const SCHEMA_OUTPUT_DIR = 'sheet_schema';
    private const NON_TARGET_SPREADSHEET_NAME = [
        '説明',
        '属性参照',
        '_ref_list',
        '_ref_name',
        '_ref_DebugLocalize',
        '_ref_releaseKey',
    ];

    public function __construct(
        private CSVOperator $csvOperator,
        private SpreadSheetFetchService $spreadSheetService,
    ) {
    }

    /**
     * 全スプレッドシートのヘッダー情報をCSVとして出力
     *
     * @param SpreadSheetRequestEntity[] $spreadSheetRequests 取得対象のスプレッドシート
     * @return void
     */
    public function exportSchemaFiles(array $spreadSheetRequests): void
    {
        try {
            $outputDir = $this->getSchemaOutputPath();

            Log::info('SheetSchemaExportService: Start exporting schema files');

            // 対象スプレッドシートのシート一覧を取得
            $spreadSheetCsvs = $this->spreadSheetService->fetchSheets($spreadSheetRequests);

            foreach ($spreadSheetCsvs as $spreadSheetCsv) {
                // 対象外シートはスキップ
                if ($this->isNonTargetSheet($spreadSheetCsv->getTitle())) {
                    continue;
                }

                try {
                    // APCu キャッシュから生データを取得
                    $rawData = $this->spreadSheetService->getRawDataCache($spreadSheetCsv->getSheetId());

                    if (empty($rawData)) {
                        // キャッシュになければスキップ（既に getAndWriteSpreadSheetCsv で取得済みのはず）
                        Log::warning("SheetSchemaExportService: Raw data not found in cache for sheet: {$spreadSheetCsv->getTitle()}");
                        continue;
                    }

                    // ヘッダー3行を抽出
                    $headerRows = $this->extractHeaderRows($rawData);

                    if (empty($headerRows)) {
                        // ヘッダー行が見つからない場合はスキップ
                        Log::warning("SheetSchemaExportService: Header rows not found for sheet: {$spreadSheetCsv->getTitle()}");
                        continue;
                    }

                    // CSV出力
                    $fileName = $this->generateFileName($spreadSheetCsv->getTitle());
                    $filePath = "{$outputDir}/{$fileName}";

                    $this->csvOperator->write($filePath, $headerRows);
                    Log::info("SheetSchemaExportService: Exported {$fileName}");
                } catch (\Exception $e) {
                    Log::error("SheetSchemaExportService: Failed to export sheet {$spreadSheetCsv->getTitle()}: " . $e->getMessage());
                    // 個別のシートでエラーが発生しても処理を継続
                    continue;
                }
            }

            Log::info('SheetSchemaExportService: Finished exporting schema files');
        } catch (\Throwable $e) {
            Log::error('SheetSchemaExportService: Unexpected error occurred: ' . $e->getMessage());
            // 例外を外に投げずに処理を継続（データ投入処理を中断させない）
        }
    }

    /**
     * シートの生データからヘッダー行（memo行、TABLE行、ENABLE行）を抽出
     *
     * @param array $rawData
     * @return array
     */
    private function extractHeaderRows(array $rawData): array
    {
        $headerRows = [];
        $tableRowFound = false;
        $enableRowFound = false;

        foreach ($rawData as $row) {
            if (empty($row)) {
                // 空行の場合、TABLE行が見つかる前ならmemo行として扱う
                if (!$tableRowFound) {
                    $headerRows[] = $row;
                }
                continue;
            }

            // TABLE行を検出
            if (!$tableRowFound && $row[0] === SpreadSheetLabel::TABLE_NAME_IDENTIFIER) {
                $headerRows[] = $row;
                $tableRowFound = true;
                continue;
            }

            // ENABLE行を検出
            if ($tableRowFound && !$enableRowFound && $row[0] === SpreadSheetLabel::COL_NAME_IDENTIFIER) {
                $headerRows[] = $row;
                $enableRowFound = true;
                break; // ENABLE行が見つかったら終了
            }

            // TABLE行が見つかる前の非空行はmemo行として扱う
            if (!$tableRowFound) {
                $headerRows[] = $row;
            }
        }

        return $headerRows;
    }

    /**
     * ファイル名を生成: {シート名}.csv
     *
     * @param string $sheetName
     * @return string
     */
    private function generateFileName(string $sheetName): string
    {
        // 空白をアンダースコアに変換
        $cleanSheetName = preg_replace('/\s+/', '_', $sheetName);

        // preg_replaceがnullを返した場合は元の文字列を使用
        if ($cleanSheetName === null) {
            $cleanSheetName = $sheetName;
        }

        return "{$cleanSheetName}.csv";
    }

    /**
     * スキーマ出力先のパスを取得
     *
     * @return string
     */
    private function getSchemaOutputPath(): string
    {
        return config('wp_master_asset_release_admin.spreadSheetCsvDir') . '/' . self::SCHEMA_OUTPUT_DIR;
    }

    /**
     * 対象外シートかどうかを判定
     *
     * @param string $sheetName
     * @return bool
     */
    private function isNonTargetSheet(string $sheetName): bool
    {
        return in_array($sheetName, self::NON_TARGET_SPREADSHEET_NAME, true);
    }
}

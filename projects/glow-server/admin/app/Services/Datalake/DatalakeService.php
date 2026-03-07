<?php

declare(strict_types=1);

namespace App\Services\Datalake;

use App\Constants\DatalakeConstant;
use App\Entities\Datalake\DatalakeTableFileWriter;
use App\Models\GenericLogModel;
use App\Models\GenericMstModel;
use App\Models\GenericOprModel;
use App\Models\GenericUsrModel;
use App\Services\Datalake\TidbDumpService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * データレイクサービス
 */
class DatalakeService
{
    /**
     * Mstデータのテーブルの内容JSONで出力
     * @param CarbonImmutable $targetDate
     * @return Collection<string>
     */
    public function outputMstDBForJson(CarbonImmutable $targetDate): Collection
    {
        // 対象データベースの内容JSONで出力
        return $this->outputDBForJson($targetDate, GenericMstModel::class);
    }

    /**
     * Oprデータのテーブルの内容JSONで出力
     * @param CarbonImmutable $targetDate
     * @return Collection<string>
     */
    public function outputOprDBForJson(CarbonImmutable $targetDate): Collection
    {
        // 対象データベースの内容JSONで出力
        return $this->outputDBForJson($targetDate, GenericOprModel::class);
    }

    /**
     * Usrデータのテーブルの内容JSONで出力（dumpling使用）
     * @param CarbonImmutable $targetDate
     * @return Collection<string>
     */
    public function outputUsrDBForJson(CarbonImmutable $targetDate): Collection
    {
        $tidbDumpService = new TidbDumpService();
        return $tidbDumpService->dumpTidbTablesToJson('usr', $targetDate, DatalakeConstant::DISK_TEMP);
    }

    /**
     * Logデータのテーブルの内容JSONで出力（dumpling使用）
     * @param CarbonImmutable $targetDate
     * @return Collection<string>
     */
    public function outputLogDBForJson(CarbonImmutable $targetDate): Collection
    {
        $tidbDumpService = new TidbDumpService();
        return $tidbDumpService->dumpTidbTablesToJson('log', $targetDate, DatalakeConstant::DISK_TEMP);
    }

    /**
     * 対象データベースの内容JSONで出力
     * @param CarbonImmutable $targetDate
     * @param string $genericModel
     * @return Collection<string>
     */
    private function outputDBForJson(CarbonImmutable $targetDate, string $genericModel, bool $isDateRange = false): Collection
    {
        Log::info("データレイク転送:JSONファイル生成開始:{$genericModel}:{$targetDate->format('Y-m-d H:i:s')}");
        $fileNameList = collect();
        $genericMstModel = new $genericModel();
        $mstTables = $genericMstModel->showTables();
        if ($isDateRange) {
            $startDate = $targetDate->startOfDay();
            $endDate = $targetDate->endOfDay();
        }
        foreach ($mstTables as $tableName) {
            $model = (new $genericModel())->setTableName($tableName);

            $mainWriter = new DatalakeTableFileWriter(
                $tableName,
                $targetDate,
                DatalakeConstant::DISK_TEMP,
                DatalakeConstant::FILE_SIZE_LIMIT,
            );
            $fileNameList->push($mainWriter->getCurrentFileName());

            $columnWriters = collect();
            // 20250514 一旦jsonカラムのdlテーブル切り分けの出力をやめる
            /**
            $columns = $model->getColumns();
            foreach ($columns as $column) {
                if ($column->data_type === 'json') {
                    $columnWriter = new DatalakeColumnFileWriter(
                        $tableName,
                        $column->column_name,
                        $targetDate,
                        DatalakeConstant::DISK_TEMP,
                        DatalakeConstant::FILE_SIZE_LIMIT,
                    );
                    $columnWriters->put($column->column_name, $columnWriter);
                    $fileNameList->push($columnWriter->getCurrentFileName());
                }
            }
             **/

            $step = 1;
            do {
                if ($isDateRange) {
                    $records = $model->fetchByDateRange(
                        $startDate,
                        $endDate,
                        ($step - 1) * DatalakeConstant::RECORD_FETCH_LIMIT,
                        DatalakeConstant::RECORD_FETCH_LIMIT,
                    );
                }
                else {
                    $records = $model->fetchAll(
                        ($step - 1) * DatalakeConstant::RECORD_FETCH_LIMIT,
                        DatalakeConstant::RECORD_FETCH_LIMIT,
                    );
                }
                if ($records->isEmpty()) {
                    break;
                }

                foreach ($records as $record) {
                    $recordArray = $record->getAttributes();
                    if ($mainWriter->writeJson($recordArray)) {
                        $fileNameList->push($mainWriter->getCurrentFileName());
                    }

                    foreach ($columnWriters as $columnName => $columnWriter) {
                        $jsonData = $recordArray[$columnName] ?? null;
                        if ($jsonData === null) {
                            continue;
                        }
                        // JSONデータに親レコードのIDを紐づける(idを先頭にする)
                        $jsonArray = json_decode($jsonData, true);
                        $jsonArray = ['parent_id' => $recordArray['id'] ?? ''] + $jsonArray;
                        $jsonData = json_encode($jsonArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        if ($columnWriter->writeRaw($jsonData)) {
                            $fileNameList->push($columnWriter->getCurrentFileName());
                        }
                    }
                }

                $step++;
                unset($records);
            } while (true);

            $mainWriter->finalize();

            foreach ($columnWriters as $writer) {
                $writer->finalize();
            }
        }
        Log::info("データレイク転送:JSONファイル生成終了:{$genericModel}");
        return $fileNameList;
    }

    /**
     * ログを圧縮しGCSにアップロードする（usr/log用 - 既に圧縮済みファイル）
     *
     * @param string $pathPrefix
     * @param CarbonImmutable $targetTime
     * @param Collection $tempFileNames
     * @param string $tempDisk
     * @param string $uploadDisk
     * @param string|null $dayPath
     * @return bool
     */
    public function compressAndUploadToGcs(
        string $pathPrefix,
        CarbonImmutable $targetTime,
        Collection $tempFileNames,
        string $tempDisk,
        string $uploadDisk,
        ?string $dayPath = null
    ): bool {
        $tempStorage = Storage::disk($tempDisk);
        $uploadStorage = Storage::disk($uploadDisk);
        $result = true;

        // gz圧縮 & 保存 & 元ファイルを削除 & 転送実行
        foreach ($tempFileNames as $tempFileName) {
            // ファイル存在確認
            if (!$tempStorage->exists($tempFileName)) {
                Log::error("データレイク転送:ファイルが見つかりません: {$tempFileName}");
                $result = false;
                break;
            }

            // ファイル内容取得
            $fileContent = $tempStorage->get($tempFileName);
            if ($fileContent === null) {
                Log::error("データレイク転送:ファイル読み込み失敗: {$tempFileName}");
                $result = false;
                break;
            }

            // $compressedFileName = "{$tempFileName}.gz";
            $compressedFileName = $tempFileName;
            // $tempStorage->put($compressedFileName, gzencode($fileContent, 9));
            // $tempStorage->delete($tempFileName);

            $compressedFilePath = $tempStorage->path($compressedFileName);

            // 日付部分を決定（dayPathが指定されている場合はそれを使用、未指定の場合は既存の'd'フォーマット）
            $dayPart = $dayPath ?? $targetTime->format('d');

            $uploadFilePath = sprintf(
                DatalakeConstant::GCS_FILE_UPLOAD_PATH,
                $pathPrefix,
                $targetTime->format('Y'),
                $targetTime->format('m'),
                $dayPart,
                strstr($compressedFileName, '-', true), // テーブル名抜き出し
                $compressedFileName
            );
            try {
                $result = $uploadStorage->put($uploadFilePath, file_get_contents($compressedFilePath));
            } catch (\Exception $e) {
                Log::error(
                    "データレイク転送:GCSアップロードエラー: " . $e->getMessage(),
                    ['trace' => $e->getTraceAsString()]
                );
                $result = false;
            }
            $tempStorage->delete($compressedFileName);
            if (!$result) {
                Log::info("データレイク転送:転送強制終了:{$uploadFilePath}");
                break;
            }
            Log::info("データレイク転送:転送完了:{$uploadFilePath}");
        }
        // 途中で中断してしまった場合は、アップロードしようとしたファイルを削除
        if ($tempFileNames->isNotEmpty()) {
            foreach ($tempFileNames as $tempFileName) {
                $tempStorage->delete($tempFileName);
            }
        }
        return $result;
    }

    /**
     * ログを圧縮しGCSにアップロードする（mst/opr用 - 圧縮処理あり）
     *
     * @param string $pathPrefix
     * @param CarbonImmutable $targetTime
     * @param Collection $tempFileNames
     * @param string $tempDisk
     * @param string $uploadDisk
     * @return bool
     */
    public function compressAndUploadToGcsWithCompression(
        string $pathPrefix,
        CarbonImmutable $targetTime,
        Collection $tempFileNames,
        string $tempDisk,
        string $uploadDisk,
    ): bool {
        $tempStorage = Storage::disk($tempDisk);
        $uploadStorage = Storage::disk($uploadDisk);
        $result = true;

        // gz圧縮 & 保存 & 元ファイルを削除 & 転送実行
        foreach ($tempFileNames as $tempFileName) {
            // ファイル存在確認
            if (!$tempStorage->exists($tempFileName)) {
                Log::error("データレイク転送:ファイルが見つかりません: {$tempFileName}");
                $result = false;
                break;
            }

            // ファイル内容取得
            $fileContent = $tempStorage->get($tempFileName);
            if ($fileContent === null) {
                Log::error("データレイク転送:ファイル読み込み失敗: {$tempFileName}");
                $result = false;
                break;
            }

            // gz圧縮処理（修正前の状態）
            $compressedFileName = "{$tempFileName}.gz";
            $tempStorage->put($compressedFileName, gzencode($fileContent, 9));
            $tempStorage->delete($tempFileName);

            $compressedFilePath = $tempStorage->path($compressedFileName);
            $uploadFilePath = sprintf(
                DatalakeConstant::GCS_FILE_UPLOAD_PATH,
                $pathPrefix,
                $targetTime->format('Y'),
                $targetTime->format('m'),
                $targetTime->format('d'),
                strstr($compressedFileName, '-', true), // テーブル名抜き出し
                $compressedFileName
            );
            try {
                $result = $uploadStorage->put($uploadFilePath, file_get_contents($compressedFilePath));
            } catch (\Exception $e) {
                Log::error(
                    "データレイク転送:GCSアップロードエラー: " . $e->getMessage(),
                    ['trace' => $e->getTraceAsString()]
                );
                $result = false;
            }
            $tempStorage->delete($compressedFileName);
            if (!$result) {
                Log::info("データレイク転送:転送強制終了:{$uploadFilePath}");
                break;
            }
            Log::info("データレイク転送:転送完了:{$uploadFilePath}");
        }
        // 途中で中断してしまった場合は、アップロードしようとしたファイルを削除
        if ($tempFileNames->isNotEmpty()) {
            foreach ($tempFileNames as $tempFileName) {
                $tempStorage->delete($tempFileName);
            }
        }
        return $result;
    }
}

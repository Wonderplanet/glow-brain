<?php

namespace App\Services\MasterData;

use App\Entities\MasterData\SpreadSheetCsvEntity;
use App\Entities\MasterData\SpreadSheetRequestEntity;
use App\Operators\CSVOperator;
use App\Operators\GitOperator;
use App\Operators\GoogleDriveOperator;
use App\Operators\GoogleSpreadSheetOperator;
use App\Constants\GoogleDriveMimeType;

class SpreadSheetFetchService
{
    /**
     * スプレッドシートのデータを取得してCSVとして保存する
     * @param SpreadSheetRequestEntity[] $target 取得対象のスプレッドシート、空なら全部
     * @return void
     */
    public function getAndWriteSpreadSheetCsv(array $target = []): void
    {
        // シート取得
        $spreadSheets = $this->fetch($target);

        // CSV書き出し
        $csv = new CSVOperator();
        foreach ($spreadSheets as $entity) {
            $csv->write(config('admin.spreadSheetCsvDir') . "/" . $entity->getTitle() . ".csv", $entity->getData());
        }
    }

    /**
     * スプレッドシートをデータ付きでを取得してSpreadSheetCsvEntityとして返す
     * @param SpreadSheetRequestEntity[] $target 取得対象のスプレッドシート、空なら全部
     * @return SpreadSheetCsvEntity[]
     */
    public function fetch(array $target): array
    {
        $sheets = $this->fetchSheets($target);
        return $this->fetchValues($target, $sheets);
    }

    /**
     * スプレッドシート一覧を返す
     * @param SpreadSheetRequestEntity[] $target 取得対象のスプレッドシート、空なら全部
     * @return SpreadSheetCsvEntity[] データは空
     */
    public function fetchSheets(array $target = []): array
    {
        // シート取得
        $googleCredentialPath = config('admin.googleCredentialPath');
        $drive = new GoogleDriveOperator($googleCredentialPath);
        if (empty($target)) {
            $fileIds = $drive->getFileIdList(GoogleDriveMimeType::SpreadSheet);
        } else {
            $fileIds = array_map(fn(SpreadSheetRequestEntity $e): string => $e->getFileId(), $target);
            $fileIds = array_unique($fileIds);
        }

        $sheetOperator = new GoogleSpreadSheetOperator($googleCredentialPath);
        $sheets = [];
        foreach ($fileIds as $fileId) {
            $result = $sheetOperator->getSheets($fileId);
            foreach ($result as $sheet) {
                $sheets[] =  new SpreadSheetCsvEntity($fileId, $sheet, []);
            }
        }
        return $sheets;
    }

    /**
     * スプレッドシートのデータを取得してSpreadSheetCsvEntityとして返す
     * @param SpreadSheetRequestEntity[] $target
     * @param SpreadSheetCsvEntity[] $sheets
     * @return SpreadSheetCsvEntity[]
     */
    public function fetchValues(array $target = [], array $sheets = []): array
    {
        $googleCredentialPath = config('admin.googleCredentialPath');
        $sheetOperator = new GoogleSpreadSheetOperator($googleCredentialPath);

        if (empty($sheets)) {
            $sheets = $this->fetchSheets();
        }

        if (empty($target)) {
            $sheetIds = [];
        } else {
            $sheetIds = array_map(fn($e): string => $e->getSheetId(), $target);
            $sheetIds = array_unique($sheetIds);
        }

        $spreadSheets = [];
        foreach ($sheets as $entity) {
            if (!empty($sheetIds) && !in_array($entity->getSheetId(), $sheetIds)) {
                // $targetになければ取得しない
                continue;
            }

            // タイトル名にアンダーバーがある場合は
            // アンダーバーまでをタイトル名とし、一つにまとめて使用する
            $value = $sheetOperator->getSheetValues($entity->getFileId(), $entity->getTitle());
            $pos = strrpos($entity->getTitle(), '_');
            if ($pos === false) {
                $entity->setData($value);
                $spreadSheets[$entity->getTitle()] = $entity;
            } else {
                $title = substr($entity->getTitle(), 0, $pos);
                $spreadSheets[$title]->mergeData($value);
            }
        }
        return $spreadSheets;
    }
}

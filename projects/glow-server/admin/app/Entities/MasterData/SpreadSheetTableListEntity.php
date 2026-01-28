<?php

namespace App\Entities\MasterData;

/**
 * データ投入対象候補のテーブルをまとめたリストシートの、1行ごとのデータを格納するEntity
 * @see https://docs.google.com/spreadsheets/d/1aURBLg7OAj142a2gqwjis7KV2D-ccEwgIdqQkC9dT54/edit?gid=0#gid=0
 */
class SpreadSheetTableListEntity
{
    private string $fileName;
    private string $fileId;
    private string $sheetName;
    private int $sheetId;
    private string $url;

    /**
     * @param array<mixed> $data
     */
    public function __construct(
        array $data
    ) {
        $this->fileName = $data['fileName'] ?? '';
        $this->fileId = $data['fileId'] ?? '';
        $this->sheetName = $data['sheetName'] ?? '';
        $this->sheetId = $data['sheetId'] ?? '';
        $this->url = $data['link'] ?? '';
    }

    public function getFileName() : string
    {
        return $this->fileName;
    }

    public function getFileId() : string
    {
        return $this->fileId;
    }

    public function getSheetName() : string
    {
        return $this->sheetName;
    }

    public function getSheetId() : int
    {
        return $this->sheetId;
    }

    public function getUrl() : string
    {
        return $this->url;
    }
}

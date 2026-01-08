<?php

namespace App\Entities\MasterData;

use Google\Service\Sheets\Sheet;

class SpreadSheetCsvEntity
{
    private array $data;
    private string $fileId;
    private int $sheetId;
    private string $title;
    private string $url;

    public function __construct(string $fileId, Sheet $sheet, array $data)
    {
        $this->data = $data;
        $this->title = $sheet->getProperties()->getTitle();
        $this->sheetId = $sheet->getProperties()->getSheetId();
        $this->fileId = $fileId;
        $this->url = "https://docs.google.com/spreadsheets/d/${fileId}/edit#gid={$sheet->getProperties()->getSheetId()}";
    }

    public function getData() : array
    {
        return $this->data;
    }
    public function setData(array $data) : void
    {
        $this->data = $data;
    }

    public function mergeData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getFileId() : string
    {
        return $this->fileId;
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

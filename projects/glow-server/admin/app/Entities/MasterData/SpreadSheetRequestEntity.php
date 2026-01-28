<?php

namespace App\Entities\MasterData;

class SpreadSheetRequestEntity
{
    private string $fileId;
    private string $sheetId;

    public function __construct(string $fileId, string $sheetId)
    {
        $this->fileId = $fileId;
        $this->sheetId = $sheetId;
    }

    public function getFileId() : string
    {
        return $this->fileId;
    }
    public function getSheetId() : string
    {
        return $this->sheetId;
    }

}

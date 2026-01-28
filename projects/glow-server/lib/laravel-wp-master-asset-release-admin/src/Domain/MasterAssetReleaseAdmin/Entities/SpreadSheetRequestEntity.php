<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities;

/**
 *  マスターデータインポートv2用
 * v1のものをそのまま移植
 */
class SpreadSheetRequestEntity
{
    private string $fileId;
    private string $fileName;
    private string $sheetId;

    public function __construct(string $fileId, string $fileName, string $sheetId)
    {
        $this->fileId = $fileId;
        $this->fileName = $fileName;
        $this->sheetId = $sheetId;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getSheetId(): string
    {
        return $this->sheetId;
    }
}

<?php

namespace App\Entities\MasterData;

class GitDiffEntity
{
    private string $sheetName;
    private array $rawData;

    private int $line;
    private int $count;
    private array $header;
    private array $oldData;
    private array $newData;

    public function __construct(string $sheetName, array $rawData, array $header)
    {
        $this->sheetName = pathinfo($sheetName, PATHINFO_FILENAME);
        $this->rawData = $rawData;
        $this->header = $header;
        $this->parse();
    }

    private function parse() : void
    {
        $this->line = isset($this->rawData['line']) ? (int) $this->rawData['line'] : -1;
        $this->count = isset($this->rawData['count']) ? (int) $this->rawData['count'] : -1;

        $this->oldData = $this->rawData['old'] ?? [];
        $this->newData = $this->rawData['new'] ?? [];
    }

    public function getSheetName() : string
    {
        return $this->sheetName;
    }
    public function getLine() : int
    {
        return $this->line;
    }
    public function getCount() : int
    {
        return $this->count;
    }
    public function getHeader() : array
    {
        return $this->header;
    }
    public function getOldData() : array
    {
        return $this->oldData;
    }
    public function getNewData() : array
    {
        return $this->newData;
    }
}

<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities;

use Illuminate\Support\Facades\Log;

class RawDatabaseCsvEntity
{
    private array $data;
    private string $title; // スプレッドシート由来の名前
    private string $tableName;

    public function __construct(array $data, string $title)
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function getData() : array
    {
        return $this->data;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function getTableName() : string
    {
        return $this->tableName;
    }
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function hasData(): bool
    {
        return isset($this->data) && count($this->data) > 0;
    }

}

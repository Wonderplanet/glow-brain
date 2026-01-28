<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities;

/***
 * マスターデータ管理ツールv2で使用するクラス
 */
class DatabaseCsvEntity
{
    private array $data;
    private string $title; // クラス名からMasterを取り除いたもの

    private string $releaseKey;

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

    public function getReleaseKey(): string
    {
        return $this->releaseKey;
    }

    public function setReleaseKey(string $releaseKey): void
    {
        $this->releaseKey = $releaseKey;
    }

    public function hasData(): bool
    {
        return isset($this->data) && count($this->data) > 0;
    }
}

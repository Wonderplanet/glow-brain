<?php

namespace App\Entities\MasterData;

class DatabaseCsvEntity
{
    private array $data;
    private string $title; // クラス名からMasterを取り除いたもの

    private string $releaseKey;
    private string $gitRevision;

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

    public function getGitRevision(): string
    {
        return $this->gitRevision;
    }
    public function setGitRevision(string $gitRevision): void
    {
        $this->gitRevision = $gitRevision;
    }
    public function getVersion(): string
    {
        return $this->getReleaseKey() . '_' . $this->getGitRevision();
    }

    public function hasData(): bool
    {
        return isset($this->data) && count($this->data) > 0;
    }
}

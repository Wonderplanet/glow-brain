<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstSeriesI18nEntity
{
    public function __construct(
        private string $id,
        private string $mst_series_id,
        private string $language,
        private string $name,
        private string $prefix_word,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstSeriesId(): string
    {
        return $this->mst_series_id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrefixWord(): string
    {
        return $this->prefix_word;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkEntity
{
    public function __construct(
        private string $id,
        private string $mst_series_id,
        private int $outpost_additional_hp,
        private string $asset_key,
        private int $sort_order,
        private string $rarity,
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

    public function getOutpostAdditionalHp(): int
    {
        return $this->outpost_additional_hp;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getSortOrder(): int
    {
        return $this->sort_order;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

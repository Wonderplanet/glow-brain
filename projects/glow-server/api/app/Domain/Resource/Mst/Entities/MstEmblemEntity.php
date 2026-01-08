<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstEmblemEntity
{
    public function __construct(
        private string $id,
        private string $emblem_type,
        private string $mst_series_id,
        private string $asset_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmblemType(): string
    {
        return $this->emblem_type;
    }

    public function getMstSeriesId(): string
    {
        return $this->mst_series_id;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstSeriesEntity
{
    public function __construct(
        private string $id,
        private string $jump_plus_url,
        private string $asset_key,
        private string $banner_asset_key,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getJumpPlusUrl(): string
    {
        return $this->jump_plus_url;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getBannerAssetKey(): string
    {
        return $this->banner_asset_key;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

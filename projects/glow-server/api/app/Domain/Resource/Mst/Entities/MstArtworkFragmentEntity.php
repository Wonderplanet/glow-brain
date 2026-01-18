<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkFragmentEntity
{
    public function __construct(
        private string $id,
        private string $mst_artwork_id,
        private ?string $drop_group_id,
        private ?int $drop_percentage,
        private int $asset_num,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkId(): string
    {
        return $this->mst_artwork_id;
    }

    public function getDropGroupId(): ?string
    {
        return $this->drop_group_id;
    }

    public function getDropPercentage(): ?int
    {
        return $this->drop_percentage;
    }

    public function getAssetNum(): int
    {
        return $this->asset_num;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

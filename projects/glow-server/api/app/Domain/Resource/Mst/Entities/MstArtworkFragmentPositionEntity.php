<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkFragmentPositionEntity
{
    public function __construct(
        private string $id,
        private string $mst_artwork_fragment_id,
        private int $position,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkFragmentId(): string
    {
        return $this->mst_artwork_fragment_id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

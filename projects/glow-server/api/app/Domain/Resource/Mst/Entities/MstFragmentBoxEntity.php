<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstFragmentBoxEntity
{
    public function __construct(
        private string $id,
        private string $mst_item_id,
        private string $mst_fragment_box_group_id,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstItemId(): string
    {
        return $this->mst_item_id;
    }

    public function getMstFragmentBoxGroupId(): string
    {
        return $this->mst_fragment_box_group_id;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

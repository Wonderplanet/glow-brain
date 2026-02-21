<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstEnemyCharacterEntity
{
    public function __construct(
        private string $id,
        private string $mstSeriesId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstSeriesId(): string
    {
        return $this->mstSeriesId;
    }
}

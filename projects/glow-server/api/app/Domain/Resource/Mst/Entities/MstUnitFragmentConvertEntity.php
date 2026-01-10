<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitFragmentConvertEntity
{
    public function __construct(
        private string $id,
        private string $unit_label,
        private int $convert_amount,
        private int $release_key,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUnitLabel(): string
    {
        return $this->unit_label;
    }

    public function getConvertAmount(): int
    {
        return $this->convert_amount;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}

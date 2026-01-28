<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstConfigEntity
{
    public function __construct(
        private string $id,
        private string $key,
        private string $value,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

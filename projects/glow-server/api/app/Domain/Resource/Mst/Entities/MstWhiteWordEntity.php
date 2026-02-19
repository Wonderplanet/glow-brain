<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstWhiteWordEntity
{
    public function __construct(
        private string $id,
        private string $word,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getWord(): string
    {
        return $this->word;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPvpI18nEntity
{
    public function __construct(
        private string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}

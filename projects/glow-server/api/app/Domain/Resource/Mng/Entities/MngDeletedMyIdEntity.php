<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

class MngDeletedMyIdEntity
{
    public function __construct(
        private string $id,
        private string $myId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMyId(): string
    {
        return $this->myId;
    }
}

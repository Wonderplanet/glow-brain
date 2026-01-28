<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstDummyUserArtworkEntity
{
    public function __construct(
        private string $id,
        private string $mstDummyUserId,
        private string $mstArtworkId,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstDummyUserId(): string
    {
        return $this->mstDummyUserId;
    }

    public function getMstArtworkId(): string
    {
        return $this->mstArtworkId;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->id,
            'mstDummyUserId' => $this->mstDummyUserId,
            'mstArtworkId' => $this->mstArtworkId,
        ];
    }
}

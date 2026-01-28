<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrOutpostEntity
{
    public function __construct(
        private string $usrUserId,
        private string $mst_outpost_id,
        private ?string $mst_artwork_id,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getMstOutpostId(): string
    {
        return $this->mst_outpost_id;
    }

    public function getMstArtworkId(): ?string
    {
        return $this->mst_artwork_id;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'mst_outpost_id' => $this->mst_outpost_id,
            'mst_artwork_id' => $this->mst_artwork_id,
        ];
    }
}

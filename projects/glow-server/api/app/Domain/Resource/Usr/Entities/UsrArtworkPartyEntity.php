<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

use App\Domain\Common\Utils\StringUtil;
use Illuminate\Support\Collection;

class UsrArtworkPartyEntity
{
    public function __construct(
        private string $id,
        private string $usr_user_id,
        private int $party_no,
        private string $party_name,
        private string $mst_artwork_id_1,
        private ?string $mst_artwork_id_2,
        private ?string $mst_artwork_id_3,
        private ?string $mst_artwork_id_4,
        private ?string $mst_artwork_id_5,
        private ?string $mst_artwork_id_6,
        private ?string $mst_artwork_id_7,
        private ?string $mst_artwork_id_8,
        private ?string $mst_artwork_id_9,
        private ?string $mst_artwork_id_10,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getPartyNo(): int
    {
        return $this->party_no;
    }

    public function getPartyName(): string
    {
        return $this->party_name;
    }

    public function getMstArtworkId1(): string
    {
        return $this->mst_artwork_id_1;
    }

    public function getMstArtworkId2(): ?string
    {
        return $this->mst_artwork_id_2;
    }

    public function getMstArtworkId3(): ?string
    {
        return $this->mst_artwork_id_3;
    }

    public function getMstArtworkId4(): ?string
    {
        return $this->mst_artwork_id_4;
    }

    public function getMstArtworkId5(): ?string
    {
        return $this->mst_artwork_id_5;
    }

    public function getMstArtworkId6(): ?string
    {
        return $this->mst_artwork_id_6;
    }

    public function getMstArtworkId7(): ?string
    {
        return $this->mst_artwork_id_7;
    }

    public function getMstArtworkId8(): ?string
    {
        return $this->mst_artwork_id_8;
    }

    public function getMstArtworkId9(): ?string
    {
        return $this->mst_artwork_id_9;
    }

    public function getMstArtworkId10(): ?string
    {
        return $this->mst_artwork_id_10;
    }

    /**
     * @return Collection<string>
     */
    public function getMstArtworkIds(): Collection
    {
        return collect([
            $this->getMstArtworkId1(),
            $this->getMstArtworkId2(),
            $this->getMstArtworkId3(),
            $this->getMstArtworkId4(),
            $this->getMstArtworkId5(),
            $this->getMstArtworkId6(),
            $this->getMstArtworkId7(),
            $this->getMstArtworkId8(),
            $this->getMstArtworkId9(),
            $this->getMstArtworkId10(),
        ])->filter(fn($mstArtworkId) =>  ! StringUtil::isNotSpecified($mstArtworkId));
    }
}

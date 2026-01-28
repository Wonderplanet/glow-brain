<?php

declare(strict_types=1);

namespace App\Domain\Party\Models;

use App\Domain\Resource\Usr\Entities\UsrArtworkPartyEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Illuminate\Support\Collection;

interface UsrArtworkPartyInterface extends UsrModelInterface
{
    public function getUsrUserId(): string;

    public function getPartyNo(): int;

    public function getPartyName(): string;

    public function setPartyName(string $partyName): void;

    /**
     * @param Collection<string> $mstArtworkIds
     */
    public function setArtworks(Collection $mstArtworkIds): void;

    public function getMstArtworkId1(): string;

    public function getMstArtworkId2(): ?string;

    public function getMstArtworkId3(): ?string;

    public function getMstArtworkId4(): ?string;

    public function getMstArtworkId5(): ?string;

    public function getMstArtworkId6(): ?string;

    public function getMstArtworkId7(): ?string;

    public function getMstArtworkId8(): ?string;

    public function getMstArtworkId9(): ?string;

    public function getMstArtworkId10(): ?string;

    /**
     * @return Collection<string>
     */
    public function getMstArtworkIds(): Collection;

    public function toEntity(): UsrArtworkPartyEntity;
}

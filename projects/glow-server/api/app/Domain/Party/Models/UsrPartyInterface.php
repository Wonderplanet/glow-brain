<?php

declare(strict_types=1);

namespace App\Domain\Party\Models;

use App\Domain\Resource\Usr\Entities\UsrPartyEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Illuminate\Support\Collection;

interface UsrPartyInterface extends UsrModelInterface
{
    public function getUsrUserId(): string;

    public function getPartyNo(): int;

    public function getPartyName(): string;

    public function setPartyName(string $partyName): void;

    public function setUnits(Collection $usrUnitIds): void;

    public function getUsrUnitId1(): string;

    public function getUsrUnitId2(): ?string;

    public function getUsrUnitId3(): ?string;

    public function getUsrUnitId4(): ?string;

    public function getUsrUnitId5(): ?string;

    public function getUsrUnitId6(): ?string;

    public function getUsrUnitId7(): ?string;

    public function getUsrUnitId8(): ?string;

    public function getUsrUnitId9(): ?string;

    public function getUsrUnitId10(): ?string;

    /**
     * @return Collection<string>
     */
    public function getUsrUnitIds(): Collection;

    public function toEntity(): UsrPartyEntity;
}

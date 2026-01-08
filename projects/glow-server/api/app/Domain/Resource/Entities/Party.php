<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use App\Domain\Resource\Usr\Entities\UsrPartyEntity;
use Illuminate\Support\Collection;

class Party
{
    /**
     * @param ?UsrPartyEntity $usrParty
     * @param Collection<Unit> $units
     */
    public function __construct(
        private readonly ?UsrPartyEntity $usrParty,
        private readonly Collection $units,
    ) {
    }

    public function getUsrParty(): ?UsrPartyEntity
    {
        return $this->usrParty;
    }

    public function getUnits(): Collection
    {
        return $this->units;
    }

    public function getUsrUnitIds(): Collection
    {
        return $this->units->map(
            fn (Unit $unit) => $unit->getUsrUnit()->getUsrUnitId()
        );
    }

    public function getMstUnitIds(): Collection
    {
        return $this->units->map(
            fn (Unit $unit) => $unit->getUsrUnit()->getMstUnitId()
        );
    }
}

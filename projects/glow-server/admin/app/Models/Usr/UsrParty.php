<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Party\Models\Eloquent\UsrParty as BaseUsrParty;
use Illuminate\Support\Collection;

class UsrParty extends BaseUsrParty
{
    protected $connection = Database::TIDB_CONNECTION;

    /**
     * @return Collection<string>
     */
    public function getUsrUnitIds(): Collection
    {
        return collect([
            $this->getUsrUnitId1(),
            $this->getUsrUnitId2(),
            $this->getUsrUnitId3(),
            $this->getUsrUnitId4(),
            $this->getUsrUnitId5(),
            $this->getUsrUnitId6(),
            $this->getUsrUnitId7(),
            $this->getUsrUnitId8(),
            $this->getUsrUnitId9(),
            $this->getUsrUnitId10(),
        ])->filter(fn($usrUnitId) => $usrUnitId !== null);
    }
}

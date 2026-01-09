<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;

class Unit
{
    public function __construct(
        private MstUnitEntity $mstUnit,
        private UsrUnitEntity $usrUnit,
    ) {
    }

    public function getMstUnit(): MstUnitEntity
    {
        return $this->mstUnit;
    }

    public function getUsrUnit(): UsrUnitEntity
    {
        return $this->usrUnit;
    }

    public function toCheatCheckUnit(): CheatCheckUnit
    {
        return new CheatCheckUnit(
            $this->mstUnit->getId(),
            $this->usrUnit->getLevel(),
            $this->usrUnit->getRank(),
            $this->usrUnit->getGradeLevel(),
        );
    }
}

<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstDummyUserUnit as BaseMstDummyUserUnit;

class MstDummyUserUnit extends BaseMstDummyUserUnit implements IAssetImage
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_unit()
    {
        return $this->hasOne(MstUnit::class, 'id', 'mst_unit_id');
    }


    public function makeAssetPath(): ?string
    {
        return $this->mst_unit ? $this->mst_unit->makeAssetPath() : null;
    }

    public function makeBgPath(): ?string
    {
        return $this->mst_unit ? $this->mst_unit->makeBgPath() : null;
    }
}

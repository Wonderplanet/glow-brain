<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPvp as BaseMstPvp;

class MstPvp extends BaseMstPvp
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_pvp_i18n()
    {
        return $this->hasOne(MstPvpI18n::class, 'mst_pvp_id', 'id');
    }

    public function getName(): string
    {
        return $this->mst_pvp_i18n?->name ?? '';
    }
}

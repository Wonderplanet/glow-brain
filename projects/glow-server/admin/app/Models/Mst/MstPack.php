<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPack as BaseMstPack;
use App\Domain\Resource\Mst\Models\MstPackI18n;

class MstPack extends BaseMstPack
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_pack_i18n()
    {
        return $this->hasOne(MstPackI18n::class, 'mst_pack_id', 'id');
    }

    public function mst_pack_contents()
    {
        return $this->hasMany(MstPackContent::class, 'mst_pack_id', 'id');
    }
}

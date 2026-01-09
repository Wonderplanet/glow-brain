<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Item\Models\Eloquent\UsrItem as BaseUsrItem;
use App\Models\Mst\MstItem;

class UsrItem extends BaseUsrItem
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_item()
    {
        return $this->hasOne(MstItem::class, 'id', 'mst_item_id');
    }
}


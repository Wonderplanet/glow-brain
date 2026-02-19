<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Shop\Models\UsrShopItem as BaseUsrShopItem;

class UsrShopItem extends BaseUsrShopItem
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_shop_item()
    {
        return $this->hasOne(MstShopItem::class, 'id', 'mst_shop_item_id');
    }
}


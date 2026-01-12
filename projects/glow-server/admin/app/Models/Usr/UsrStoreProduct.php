<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Shop\Models\UsrStoreProduct as BaseUsrStoreProduct;
use App\Models\Opr\OprProduct;

class UsrStoreProduct extends BaseUsrStoreProduct
{
    protected $connection = Database::TIDB_CONNECTION;

    public function opr_product()
    {
        return $this->hasOne(OprProduct::class, 'id', 'product_sub_id');
    }
}

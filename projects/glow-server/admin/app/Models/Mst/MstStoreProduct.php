<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstStoreProduct as BaseMstStoreProduct;
use App\Domain\Resource\Mst\Models\MstStoreProductI18n;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MstStoreProduct extends BaseMstStoreProduct
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_store_product_i18n(): HasOne
    {
        return $this->hasOne(MstStoreProductI18n::class, 'mst_store_product_id', 'id');
    }
}

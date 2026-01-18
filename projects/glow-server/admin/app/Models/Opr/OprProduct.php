<?php

namespace App\Models\Opr;

use App\Constants\Database;
use App\Constants\ProductType;
use App\Domain\Resource\Mst\Models\OprProduct as BaseOprProduct;
use App\Models\Mst\MstPack;
use App\Models\Mst\MstShopPass;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProductI18n;
use App\Models\Usr\UsrStoreProduct;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OprProduct extends BaseOprProduct
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function usr_store_product()
    {
        return $this->hasOne(UsrStoreProduct::class, 'product_sub_id', 'id');
    }

    public function mst_pack(): HasOne
    {
        return $this->hasOne(MstPack::class, 'product_sub_id', 'id');
    }

    public function mst_store_product(): HasOne
    {
        return $this->hasOne(MstStoreProduct::class, 'id', 'mst_store_product_id');
    }

    public function getProductTypeLabelAttribute(): string
    {
        $productTypeEnum = ProductType::tryFrom($this->product_type);
        if ($productTypeEnum === null) {
            return '';
        }
        return $productTypeEnum->label();
    }

    public function mst_shop_pass(): HasOne
    {
        return $this->hasOne(MstShopPass::class, 'opr_product_id', 'id');
    }

    public function opr_product_i18n(): HasOne
    {
        return $this->hasOne(OprProductI18n::class, 'opr_product_id', 'id');
    }

    public function getProductInfoAttribute(): string
    {
        if ($this->product_type === ProductType::DIAMOND->value) {
            return $this->getProductTypeLabelAttribute() . 'x' . $this->paid_amount;
        } else {
            if ($this->mst_pack) {
                return $this->mst_pack->mst_pack_i18n->name ?? '';
            }
            else if ($this->mst_shop_pass) {
                return $this->mst_shop_pass?->mst_shop_pass_i18n->name ?? '';
            }
        }
    }
}

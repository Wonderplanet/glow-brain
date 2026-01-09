<?php

declare(strict_types=1);

namespace App\Entities\Product;

use App\Models\Mst\MstShopPass;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProduct;

class ProductPassInfo extends ProductInfo
{
    public function __construct(
        OprProduct $oprProduct,
        MstStoreProduct $mstStoreProduct,
        private MstShopPass $mstShopPass,
    ) {
        parent::__construct($oprProduct, $mstStoreProduct);
    }

    public function getName(): string
    {
        // 同名の別商品を区別するために商品IDを付与
        $baseName = $this->mstShopPass->mst_shop_pass_i18n->name ?? $this->mstShopPass->id;
        return "{$baseName}_{$this->oprProduct->getId()}";
    }

    public function getPassDurationDays(): int
    {
        return $this->mstShopPass->pass_duration_days;
    }
}

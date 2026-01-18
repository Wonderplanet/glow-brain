<?php

declare(strict_types=1);

namespace App\Entities\Product;

use App\Models\Mst\MstPack;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProduct;

class ProductPackInfo extends ProductInfo
{
    public function __construct(
        OprProduct $oprProduct,
        MstStoreProduct $mstStoreProduct,
        private MstPack $mstPack,
    ) {
        parent::__construct($oprProduct, $mstStoreProduct);
    }

    public function getName(): string
    {
        // 同名の別商品を区別するために商品IDを付与
        $baseName = $this->mstPack->mst_pack_i18n->name ?? $this->mstPack->id;
        return "{$baseName}_{$this->oprProduct->getId()}";
    }
}

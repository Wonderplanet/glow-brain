<?php

declare(strict_types=1);

namespace App\Entities\Product;

use App\Constants\ProductType;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProduct;

class ProductDiamondInfo extends ProductInfo
{
    public function __construct(
        OprProduct $oprProduct,
        MstStoreProduct $mstStoreProduct
    ) {
        parent::__construct($oprProduct, $mstStoreProduct);
    }

    public function getName(): string
    {
        return ProductType::DIAMOND->label() . 'x' . $this->getPaidAmount() . '_' . $this->oprProduct->getId();
    }
}

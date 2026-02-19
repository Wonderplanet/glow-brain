<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Models\MstStoreProduct;

class MstStoreProductRepository
{
    /**
     * IDからMstStoreProductを取得する
     *
     * @param string $mstStoreProductId
     * @return MstStoreProduct|null
     */
    public function findById(string $mstStoreProductId): ?MstStoreProduct
    {
        if ($mstStoreProductId === '') {
            return null;
        }

        return MstStoreProduct::query()
            ->where('id', $mstStoreProductId)
            ->first() ?? null;
    }
}

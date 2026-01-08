<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Models\OprProduct;

class OprProductRepository
{
    /**
     * IDからOprProductを取得する
     *
     * @param string $oprProductId
     * @return OprProduct|null
     */
    public function findById(string $oprProductId): ?OprProduct
    {
        return OprProduct::query()
            ->where('id', $oprProductId)
            ->first() ?? null;
    }
}

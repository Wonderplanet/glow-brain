<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Shop\Repositories\UsrStoreProductRepository;
use Carbon\CarbonImmutable;

class UsrStoreProductService
{
    public function __construct(
        private readonly UsrStoreProductRepository $usrStoreProductRepository,
    ) {
    }

    /**
     * 購入状態ステータスを更新する
     *
     * @param string $usrUserId
     * @param string $oprProductId
     * @param CarbonImmutable $now
     */
    public function purchase(string $usrUserId, string $oprProductId, CarbonImmutable $now): void
    {
        $usrStoreProduct = $this->usrStoreProductRepository->getOrCreateByOprProductId($usrUserId, $oprProductId, $now);
        $usrStoreProduct->incrementPurchaseCount();
        $this->usrStoreProductRepository->syncModel($usrStoreProduct);
    }
}

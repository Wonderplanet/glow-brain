<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Shop\Constants\ShopPurchaseHistoryConstant;
use App\Domain\Shop\Entities\CurrencyPurchase;
use Illuminate\Support\Collection;

readonly class ShopPurchaseHistoryCacheService
{
    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * プリズム購入履歴キャッシュを取得
     * @param string $usrUserId
     * @return Collection<CurrencyPurchase>|null
     */
    public function getPurchaseHistoriesCache(string $usrUserId): ?Collection
    {
        $cacheKey = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }

    /**
     * プリズム購入履歴キャッシュを設定
     * @param string     $usrUserId
     * @param Collection<CurrencyPurchase> $currencyPurchases
     * @return void
     */
    public function setPurchaseHistoriesCache(string $usrUserId, Collection $currencyPurchases): void
    {
        $cacheKey = CacheKeyUtil::getShopPurchaseHistoryKey($usrUserId);
        $ttl = ShopPurchaseHistoryConstant::HISTORY_DAYS * 24 * 60 * 60;
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $currencyPurchases, $ttl);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use Illuminate\Support\Collection;

readonly class WebStorePurchaseNotificationCacheService
{
    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * 未通知の購入通知を取得
     *
     * @param string $usrUserId
     * @return Collection<string> product_sub_id のリスト
     */
    public function getUnnotifiedPurchases(string $usrUserId): Collection
    {
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        /** @var Collection<string>|null $notifications */
        $notifications = $this->cacheClientManager->getCacheClient()->get($cacheKey);

        return collect($notifications ?? []);
    }

    /**
     * 購入通知を一括追加
     *
     * @param string $usrUserId
     * @param Collection<string> $productSubIds
     * @return void
     */
    public function addPurchaseNotifications(string $usrUserId, Collection $productSubIds): void
    {
        if ($productSubIds->isEmpty()) {
            return;
        }

        $notifications = $this->getUnnotifiedPurchases($usrUserId);

        // 既存の通知に新しい通知を追加する
        $notifications = $notifications->merge($productSubIds)->values();

        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $notifications->all());
    }

    /**
     * 未通知の商品IDを取得してクリアする
     *
     * @param string $usrUserId
     * @return Collection<string> product_sub_id のリスト
     */
    public function getUnnotifiedProductSubIdsAndClear(string $usrUserId): Collection
    {
        $notifications = $this->getUnnotifiedPurchases($usrUserId);

        // 取得後に即座にキャッシュをクリア
        if ($notifications->isNotEmpty()) {
            $this->clearNotifications($usrUserId);
        }

        return $notifications;
    }

    /**
     * 未通知の購入通知を全て削除（通知済みにマーク）
     *
     * @param string $usrUserId
     * @return void
     */
    public function clearNotifications(string $usrUserId): void
    {
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($usrUserId);
        $this->cacheClientManager->getCacheClient()->del($cacheKey);
    }
}

<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Facades;

use WonderPlanet\Domain\Common\Facades\BaseFacade;

// phpcs:disable -- コメントが120行を超えてしまうため無視
/**
 * 課金処理のFacade
 * 
 * @method static \WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity|null getStoreInfo(string $userId)
 * @method static \WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity setStoreInfo(string $userId, int $age, ?string $renotifyAt)
 * @method static void updateStoreInfo(string $userId, int $age, ?string $renotifyAt)
 * @method static \WonderPlanet\Domain\Billing\Entities\UsrStoreAllowanceEntity allowedToPurchase(string $userId, string $osPlatform, string $billingPlatform, string $productId, string $productSubId, string $deviceId, string $triggerDetail = "")
 * @method static \WonderPlanet\Domain\Billing\Entities\UsrStoreAllowanceEntity getStoreAllowance(string $userId, string $osPlatform, string $billingPlatform, string $storeProductId)
 * @method static \WonderPlanet\Domain\Billing\Entities\StoreReceipt verifyReceipt(string $billingPlatform, string $productId, string $receipt)
 * @method static boolean purchased(string $userId, string $osPlatform, string $billingPlatform, string $deviceId, \WonderPlanet\Domain\Billing\Entities\UsrStoreAllowanceEntity $storeAllowance, string $purchasePrice, string $rawPriceString, int $vipPoint, string $currencyCode, \WonderPlanet\Domain\Billing\Entities\StoreReceipt $receipt, \WonderPlanet\Domain\Currency\Entities\Trigger $trigger, string $loggingProductSubName, callable $callback)
 * @method static boolean hasStoreProductHistory(string $userId)
 * 
 * @see \WonderPlanet\Domain\Billing\Delegators\BillingDelegator
 */
// phpcs:enable
class Billing extends BaseFacade
{
    public const FACADE_ACCESSOR = 'wp-facade-billing';
}

<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Shop\Services\AppShopService;
use App\Domain\Shop\Services\ShopBillingService;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;

class AllowanceUseCase
{
    use UseCaseTrait;

    public function __construct(
        private ShopBillingService $shopBillingService,
        private AppShopService $appShopService,
        private BillingDelegator $billingDelegator,
        private AppCurrencyDelegator $appCurrencyDelegator,
        private Clock $clock,
    ) {
    }

    /**
     *
     * @param CurrentUser $currentUser
     * @param string $platform
     * @param string $billingPlatform
     * @param string $productId
     * @param string $productSubId
     * @return array{product_sub_id: string, product_id: string}
     * @throws GameException
     * @throws \Throwable
     */
    public function __invoke(
        CurrentUser $currentUser,
        string $platform,
        string $billingPlatform,
        string $productId,
        string $productSubId,
        string $language,
        string $currencyCode,
        string $price,
    ): array {
        $osPlatform = $this->appCurrencyDelegator->getOsPlatform($platform);

        $usrUserId = (string) $currentUser->getId();
        $now = $this->clock->now();

        // 有効期間内のプロダクトか確認
        $oprProduct = $this->appShopService->getValidOprProductById($productSubId, $now);

        // 購入可能かチェック
        $this->shopBillingService->validatePurchase($usrUserId, $now, $oprProduct);

        $deviceId = $this->appShopService->getDeviceId($usrUserId);

        $triggerDetail = "Insert new allowance. language: {$language} currency_code: {$currencyCode} price: {$price}";
        $usrStoreAllowance = $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $now,
            $price,
            $currencyCode,
            $osPlatform,
            $billingPlatform,
            $productId,
            $productSubId,
            $deviceId,
            $triggerDetail,
        ) {
            // 購入金額による年齢確認
            $this->appShopService->updateAndValidateUsrStoreInfoForPurchase($usrUserId, $now, $price, $currencyCode);

            return $this->billingDelegator->allowedToPurchase(
                $usrUserId,
                $osPlatform,
                $billingPlatform,
                $productId,
                $productSubId,
                $deviceId,
                $triggerDetail,
            );
        });

        return [
            'product_sub_id' => $usrStoreAllowance->product_sub_id,
            'product_id' => $this->appShopService->getProductIdByProductSubId(
                $usrStoreAllowance->product_sub_id,
                $usrStoreAllowance->billing_platform
            ),
        ];
    }
}

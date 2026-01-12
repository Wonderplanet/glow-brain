<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Shop\Constants\ShopPurchaseHistoryConstant;
use App\Domain\Shop\Entities\CurrencyPurchase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ShopPurchaseHistoryService
{
    public function __construct(
        private ShopPurchaseHistoryCacheService $shopPurchaseHistoryCacheService,
    ) {
    }

    /**
     * プリズム購入履歴を設定する
     *
     * @param string $usrUserId
     * @param string $billingPlatform
     * @param string $purchasePrice
     * @param int $purchaseAmount
     * @param string $currencyCode
     * @param CarbonImmutable $now
     */
    public function setCurrencyPurchaseHistory(
        string $usrUserId,
        string $billingPlatform,
        string $purchasePrice,
        int $purchaseAmount,
        string $currencyCode,
        CarbonImmutable $now,
    ): void {
        $purchaseHistories = $this->shopPurchaseHistoryCacheService->getPurchaseHistoriesCache($usrUserId);
        if ($purchaseHistories === null) {
            $purchaseHistories = collect();
        }
        $purchaseHistory = $purchaseHistories->get($billingPlatform);
        if ($purchaseHistory === null) {
            $purchaseHistory = collect();
        }

        $expireAt = $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS);
        $currencyPurchase = $this->generateCurrencyPurchase($purchasePrice, $purchaseAmount, $currencyCode, $now);
        $purchaseHistory->add($currencyPurchase);
        $purchaseHistory = $purchaseHistory
            ->filter(function (CurrencyPurchase $entity) use ($expireAt) {
                $purchaseAt = CarbonImmutable::parse($entity->getPurchaseAt());
                return $purchaseAt->greaterThanOrEqualTo($expireAt);
            })
            ->sortByDesc(function (CurrencyPurchase $entity) {
                return $entity->getPurchaseAt();
            })
            ->values();
        if ($purchaseHistory->count() > ShopPurchaseHistoryConstant::HISTORY_LIMIT) {
            $purchaseHistory = $purchaseHistory->take(ShopPurchaseHistoryConstant::HISTORY_LIMIT);
        }
        $purchaseHistories->put($billingPlatform, $purchaseHistory);
        $this->shopPurchaseHistoryCacheService->setPurchaseHistoriesCache($usrUserId, $purchaseHistories);
    }

    /**
     * プリズム購入履歴を取得する
     *
     * @param string $usrUserId
     * @param string $billingPlatform
     * @param CarbonImmutable $now
     * @return Collection<\App\Domain\Shop\Entities\CurrencyPurchase>
     */
    public function getCurrencyPurchaseHistory(
        string $usrUserId,
        string $billingPlatform,
        CarbonImmutable $now,
    ): Collection {
        $expireAt = $now->subDays(ShopPurchaseHistoryConstant::HISTORY_DAYS);
        $purchaseHistories = $this->shopPurchaseHistoryCacheService->getPurchaseHistoriesCache($usrUserId);
        if ($purchaseHistories === null) {
            return collect();
        }
        $purchaseHistory = $purchaseHistories->get($billingPlatform);
        if ($purchaseHistory === null) {
            return collect();
        }
        return $purchaseHistory
            ->filter(function (CurrencyPurchase $entity) use ($expireAt) {
                $purchaseAt = CarbonImmutable::parse($entity->getPurchaseAt());
                return $purchaseAt->greaterThanOrEqualTo($expireAt);
            })
            ->values();
    }

    /**
     * CurrencyPurchaseを生成する
     *
     * @param string $purchasePrice
     * @param int $purchaseAmount
     * @param string $currencyCode
     * @param CarbonImmutable $purchaseAt
     * @return CurrencyPurchase
     */
    private function generateCurrencyPurchase(
        string $purchasePrice,
        int $purchaseAmount,
        string $currencyCode,
        CarbonImmutable $purchaseAt,
    ): CurrencyPurchase {
        $formatter = new \NumberFormatter(
            ShopPurchaseHistoryConstant::NUMBER_FORMATTER_LOCAL,
            \NumberFormatter::CURRENCY
        );
        $formattedPrice = $formatter->formatCurrency((float)$purchasePrice, $currencyCode);
        // ¥マークが全角になってしまうため、半角に置換
        $formattedPrice = str_replace("￥", "¥", $formattedPrice);
        return new CurrencyPurchase(
            $formattedPrice,
            $purchaseAmount,
            $currencyCode,
            $purchaseAt->toDateTimeString(),
        );
    }
}

<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * プラットフォーム別の有償一次通貨関連のTrait
 */
trait PlatformPaidTrait
{
    abstract public function getPaidAmountApple(): int;
    abstract public function getPaidAmountGoogle(): int;
    abstract public function getFreeAmount(): int;

    /**
     * プラットフォーム別の有償一次通貨を取得する
     *
     * @param string $billingPlatform
     * @return integer
     */
    public function getPlatformPaidAmount(string $billingPlatform): int
    {
        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                return $this->getPaidAmountApple();
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                return $this->getPaidAmountGoogle();
            default:
                return 0;
        }
    }

    /**
     * プラットフォーム別の有償・無償一次通貨を取得する
     *
     * 指定されたプラットフォームで使用可能となる一次通貨の合計を取得する
     *
     * @param string $billingPlatform
     * @return integer
     */
    public function getPlatformTotalAmount(string $billingPlatform): int
    {
        return $this->getPlatformPaidAmount($billingPlatform) + $this->getFreeAmount();
    }

    /**
     * app store購入の有償一次通貨と無償一次通貨の合計を取得する
     *
     * @return integer
     */
    public function getTotalAmountAppStore(): int
    {
        return $this->getPlatformTotalAmount(CurrencyConstants::PLATFORM_APPSTORE);
    }

    /**
     * google plya購入の優勝一次通貨と無償一次通貨の合計を取得する
     *
     * @return integer
     */
    public function getTotalAmountGooglePlay(): int
    {
        return $this->getPlatformTotalAmount(CurrencyConstants::PLATFORM_GOOGLEPLAY);
    }
}

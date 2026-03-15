<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Traits\Mst;

use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * Trait for MstStoreProduct model
 *
 * モデルの共通処理を記載する
 */
trait MstStoreProductTrait
{
    /**
     * iOSのプロダクトIDを取得する
     *
     * @return string
     */
    abstract public function getProductIdIos(): string;

    /**
     * AndroidのプロダクトIDを取得する
     *
     * @return string
     */
    abstract public function getProductIdAndroid(): string;

    /**
     * $billingPlatformに応じたプロダクトIDを返す
     *
     * 対応するプロダクトIDがない場合は空文字を返す
     *
     * @param string $billingPlatform
     * @return string
     */
    public function getProductIdByBillingPlatform(string $billingPlatform): string
    {
        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                return $this->getProductIdIos();
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                return $this->getProductIdAndroid();
            default:
                return '';
        }
    }
}

<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * Fake Store向けストアサービス
 */
class FakeStorePlatformService extends BaseStorePlatformService
{
    use FakeStoreReceiptTrait;

    /**
     * レシートの検証を行う。
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt 検証したレシート情報を含むStoreReceiptオブジェクト
     */
    public function verifyReceipt(
        string $billingPlatform,
        string $productId,
        string $receipt,
    ): StoreReceipt {
        // 開発環境でなければ無効
        if (!CommonUtility::isDebuggableEnvironment()) {
            throw new WpBillingException(
                'invalid receipt, FakeStore receipt can only be used in debuggable environment.',
                ErrorCode::INVALID_ENVIRONMENT
            );
        }

        // Fake Storeのレシートであるかを検証
        if (!$this->isFakeStoreReceipt($receipt)) {
            throw new WpBillingException(
                'invalid receipt, This receipt is not Fake Store receipt',
                ErrorCode::INVALID_RECEIPT
            );
        }

        return $this->makeFakeStoreReceiptByReceiptString($productId, $receipt);
    }
}

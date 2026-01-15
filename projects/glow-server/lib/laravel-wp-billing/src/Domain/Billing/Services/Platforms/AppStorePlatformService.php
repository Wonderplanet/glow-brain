<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Entities\StoreReceiptAppStore;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\ReceiptFormatDetectionService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit1Service;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit2Service;

/**
 * AppStoreプラットフォーム向けサービス
 *
 * StoreKit1 (verifyReceipt API) とStoreKit2 (Server Notifications) の両方に対応
 * 各処理は専用サービス (StoreKit1Service, StoreKit2Service) に委譲
 * Glowは新規プロジェクトのため、StoreKit2のみを使用することを推奨
 *
 * @see https://developer.apple.com/documentation/appstorereceipts/verifyreceipt (StoreKit1 - Deprecated)
 * @see https://developer.apple.com/documentation/appstoreserverapi (StoreKit2)
 */
class AppStorePlatformService extends BaseStorePlatformService
{
    public function __construct(
        private StoreKit1Service $storeKit1Service,
        private StoreKit2Service $storeKit2Service,
        private ReceiptFormatDetectionService $receiptFormatDetectionService
    ) {
    }

    /**
     * レシートの検証を行う（StoreKit1/2自動判定版）
     *
     * レシート形式を自動判定し、適切な検証方法で処理する
     * ライブラリ使用者はStoreKit1/2の違いを意識する必要がない
     * 必要なサービスのみを遅延解決するため、メモリ効率が良い
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt StoreKit1のJSONレシートまたはStoreKit2のJWSトークン
     * @return StoreReceipt 検証したレシート情報を含むStoreReceiptオブジェクト
     */
    public function verifyReceipt(
        string $billingPlatform,
        string $productId,
        string $receipt,
    ): StoreReceipt {
        // レシート形式を自動判定
        $receiptType = $this->receiptFormatDetectionService->detectReceiptType($receipt);

        switch ($receiptType) {
            case ReceiptFormatDetectionService::RECEIPT_TYPE_STOREKIT2:
                return $this->verifyStoreKit2Transaction($billingPlatform, $productId, $receipt);

            case ReceiptFormatDetectionService::RECEIPT_TYPE_STOREKIT1:
                return $this->verifyStoreKit1Receipt($billingPlatform, $productId, $receipt);

            default:
                throw new WpBillingException(
                    'Unknown receipt format. Receipt must be StoreKit1 JSON or StoreKit2 JWS format.',
                    ErrorCode::INVALID_RECEIPT
                );
        }
    }

    /**
     * StoreKit1レシートの検証（専用サービスに委譲）
     * 必要な時のみStoreKit1Serviceを解決
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt
     */
    private function verifyStoreKit1Receipt(
        string $billingPlatform,
        string $productId,
        string $receipt,
    ): StoreReceipt {
        $response = $this->storeKit1Service->verifyReceipt($productId, $receipt);
        $storeReceipt = new StoreReceiptAppStore($productId, $receipt, $response);
        if (!$storeReceipt->getUnitqueId()) {
            throw new WpBillingException(
                'invalid receipt, transaction id is not found.',
                ErrorCode::INVALID_RECEIPT
            );
        }
        return $storeReceipt;
    }

    /**
     * StoreKit2のsignedTransactionInfoを使用したレシート検証
     * 必要な時のみStoreKit2Serviceを解決
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $signedTransactionInfo StoreKit2のsignedTransactionInfo (JWS)
     * @return StoreReceipt
     * @throws WpBillingException
     */
    private function verifyStoreKit2Transaction(
        string $billingPlatform,
        string $productId,
        string $signedTransactionInfo,
    ): StoreReceipt {
        $transactionData = $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
        $receiptInfo = $this->storeKit2Service->extractReceiptInfo($transactionData);
        $actualProductId = $receiptInfo['receipt']['in_app'][0]['product_id'] ?? '';
        if ($actualProductId !== $productId) {
            throw new WpBillingException(
                "Product ID mismatch: expected {$productId}, got {$actualProductId}",
                ErrorCode::INVALID_RECEIPT
            );
        }
        $storeReceipt = new StoreReceiptAppStore($productId, $signedTransactionInfo, $receiptInfo);
        if (!$storeReceipt->getUnitqueId()) {
            throw new WpBillingException(
                'invalid receipt, transaction id is not found.',
                ErrorCode::INVALID_RECEIPT
            );
        }
        return $storeReceipt;
    }
}

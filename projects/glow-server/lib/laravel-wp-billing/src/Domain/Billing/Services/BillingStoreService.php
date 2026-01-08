<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingDuplicateReceiptException;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\Platforms\AppStorePlatformService;
use WonderPlanet\Domain\Billing\Services\Platforms\BaseStorePlatformService;
use WonderPlanet\Domain\Billing\Services\Platforms\FakeStorePlatformService;
use WonderPlanet\Domain\Billing\Services\Platforms\GooglePlayPlatformService;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * 各種ストアプラットフォームを管理するサービス
 */
class BillingStoreService
{
    public function __construct(
        private FakeStorePlatformService $fakeStorePlatformService,
        private AppStorePlatformService $appStorePlatformService,
        private GooglePlayPlatformService $googlePlayPlatformService,
        private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository,
    ) {
    }

    /**
     * レシートを検証する
     *
     * - 各ストアに問い合わせてのレシート検証
     * - レシートに記載されているプロダクトIDと、$productIdの一致確認
     * - 検証済みレシートのデータを含むStoreReceiptオブジェクトを返す
     *
     * @param string $billingPlatform
     * @param string $productId
     * @param string $receipt
     * @return StoreReceipt
     */
    public function verifyReceipt(
        string $billingPlatform,
        string $productId,
        string $receipt,
    ): StoreReceipt {
        $storePlatformService = $this->getStorePlatformService($billingPlatform, $receipt);

        $receipt = $storePlatformService->verifyReceipt($billingPlatform, $productId, $receipt);

        // トランザクションIDの重複購入チェック
        $this->verifyReceiptUniqueId($billingPlatform, $receipt->getUnitqueId());

        return $receipt;
    }

    /**
     * レシートのuniqueIdがすでに購入済みではないか検証する
     *
     * ストアレシートのユニークIDは、そのストアで一意になるはずなので
     * 他のユーザーが使用していた場合でも、購入済みと判定する必要がある
     *
     * @param string $billingPlatform
     * @param string $receiptUniqueId
     * @return void
     * @throws WpBillingDuplicateReceiptException
     */
    private function verifyReceiptUniqueId(string $billingPlatform, string $receiptUniqueId)
    {
        // レコードを検索
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository->findByReceiptUniqueIdAndBillingPlatform(
            $receiptUniqueId,
            $billingPlatform
        );

        // 存在していたら、重複購入と判定
        if (!is_null($usrStoreProductHistory)) {
            throw new WpBillingDuplicateReceiptException($receiptUniqueId);
        }
    }

    /**
     * 購入の承認を行う
     *
     * @param StoreReceipt $storeReceipt
     * @return void
     */
    public function purchaseAcknowledge(StoreReceipt $storeReceipt)
    {
        $storePlatformService = $this->getStorePlatformServiceByReceipt($storeReceipt);

        // 課金プラットフォームのレシートを取り出して渡す
        $storePlatformService->purchaseAcknowledge($storeReceipt->getPlatformReceiptString());
    }

    /**
     * レシート別のストアプラットフォームサービスを取得する
     *
     * @param string $billingPlatform
     * @param string $receipt
     * @return BaseStorePlatformService
     */
    public function getStorePlatformService(string $billingPlatform, string $receipt): BaseStorePlatformService
    {
        // receiptの種類でFakeStoreを判断する
        if ($this->fakeStorePlatformService->isFakeStoreReceipt($receipt)) {
            if (CommonUtility::isDebuggableEnvironment()) {
                return $this->fakeStorePlatformService;
            }
            // FakeStoreのレシートは開発環境のみ有効
            throw new WpBillingException(
                'invalid receipt, FakeStore receipt can only be used in debuggable environment.',
                ErrorCode::INVALID_ENVIRONMENT
            );
        }

        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                return $this->appStorePlatformService;
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                return $this->googlePlayPlatformService;
            default:
                throw new WpBillingException(
                    "invalid billing platform. '{$billingPlatform}'",
                    ErrorCode::UNSUPPORTED_BILLING_PLATFORM
                );
        }
    }

    /**
     * レシートに対応するストアを返す
     *
     * @param StoreReceipt $storeReceipt
     * @return BaseStorePlatformService
     */
    public function getStorePlatformServiceByReceipt(StoreReceipt $storeReceipt): BaseStorePlatformService
    {
        // receiptTypeによって返すストアサービスを決定する
        switch ($storeReceipt->getReceiptType()) {
            case StoreReceipt::TYPE_FAKESTORE:
                return $this->fakeStorePlatformService;
            case StoreReceipt::TYPE_APPSTORE:
                return $this->appStorePlatformService;
            case StoreReceipt::TYPE_GOOGLEPLAY:
                return $this->googlePlayPlatformService;
            default:
                throw new WpBillingException(
                    "invalid receipt type. '{$storeReceipt->getReceiptType()}'",
                    ErrorCode::UNSUPPORTED_RECEIPT
                );
        }
    }
}

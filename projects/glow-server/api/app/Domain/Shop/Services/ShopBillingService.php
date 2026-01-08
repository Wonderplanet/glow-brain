<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Resource\Mst\Repositories\MstPackRepository;
use App\Domain\Resource\Mst\Repositories\MstShopPassRepository;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Repositories\UsrStoreProductRepository;
use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Entities\NullAllowanceCallbackEntity;
use WonderPlanet\Domain\Billing\Entities\StoreReceipt;
use WonderPlanet\Domain\Billing\Entities\UsrStoreAllowanceEntity;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class ShopBillingService
{
    public function __construct(
        private BillingDelegator $billingDelegator,
        private AppCurrencyDelegator $appCurrencyDelegator,
        private ShopService $shopService,
        private UsrStoreProductRepository $usrStoreProductRepository,
        private MstShopPassRepository $mstShopPassRepository,
        private MstPackRepository $mstPackRepository,
    ) {
    }

    /**
     * 購入可能かのチェック
     */
    public function validatePurchase(
        string $usrUserId,
        CarbonImmutable $now,
        OprProductEntity $oprProduct,
    ): void {
        // 購入回数制限チェック
        $usrStoreProduct = $this->usrStoreProductRepository->getOrCreateByOprProductId(
            $usrUserId,
            $oprProduct->getId(),
            $now,
        );
        $this->shopService->validateTradeCount(
            $oprProduct->getPurchasableCount(),
            $usrStoreProduct->getPurchaseCount(),
        );

        // 商品タイプに応じたバリデーション

        // パスの検証
        if ($oprProduct->getProductType() === ProductType::PASS->value) {
            $mstShopPass = $this->mstShopPassRepository->getByOprProductId($oprProduct->getId(), true);
            $this->shopService->validatePass($mstShopPass->getId(), $usrUserId, $now);
        }

        // パックの検証
        if ($oprProduct->getProductType() === ProductType::PACK->value) {
            $mstPack = $this->mstPackRepository->getByProductSubId($oprProduct->getId(), true);

            // 課金購入以外なら処理させない
            if (!$mstPack->isCash()) {
                throw new GameException(
                    ErrorCode::SHOP_PURCHASE_PRODUCT_TYPE_NOT_SUPPORTED,
                    "The pack is not purchasable with the current method. (mst_pack_id: {$mstPack->getId()})"
                );
            }

            $this->shopService->validateConditionPack($mstPack, $usrUserId, $now);
        }
    }

    /**
     * レシートはあるが購入許可レコード(usr_store_allowances)がない特殊ケースで
     * 決済済みなので、レシート情報をもとに、usr_store_allowancesを自動挿入し
     * 課金処理を中断せずに完了させるための処理。
     *
     * laravel-wp-framework コードコメントからコピペ:
     * 本来getメソッドで処理するべきではないが、購入許可情報があるはずなのに何らかの問題でnullになる場合の対応
     * allowanceがnullのときに実行されるコールバック関数
     * NullAllowanceCallbackEntityをもとにallowanceの自動挿入を行う
     *
     * @param string $productId リクエストされたproductSubIdに紐づくストアプロダクトID
     */
    public function getOrForceInsertAllowance(
        string $usrUserId,
        string $billingPlatform,
        string $productId,
        StoreReceipt $storeReceipt,
        string $osPlatform,
        string $deviceId,
    ): UsrStoreAllowanceEntity {
        $usrStoreAllowanceEntity = $this->billingDelegator->getOrCreateStoreAllowance(
            $usrUserId,
            $billingPlatform,
            $productId,
            function () use (
                $storeReceipt,
                $billingPlatform,
                $osPlatform,
                $productId,
                $deviceId,
            ): NullAllowanceCallbackEntity {
                $receiptProductIds = $storeReceipt->getProductIds();
                if (
                    !CommonUtility::isDebuggableEnvironment()
                    && !in_array($productId, $receiptProductIds)
                ) {
                    // リクエストされたproductIdがレシートに存在しない場合はエラー
                    throw new GameException(
                        ErrorCode::BILLING_VERIFY_RECEIPT_INVALID_RECEIPT,
                        sprintf(
                            'Request productId was not found in the receipt. request: %s, receipt: %s',
                            $productId,
                            implode(', ', $receiptProductIds),
                        )
                    );
                }

                $oprProduct = $this->appCurrencyDelegator->getOprProductByProductId(
                    $productId,
                    $billingPlatform,
                    $storeReceipt->getPurchaseDate(),
                );
                if (is_null($oprProduct)) {
                    throw new GameException(
                        ErrorCode::MST_NOT_FOUND,
                        sprintf(
                            'opr_products record not found for product_id: %s, billing_platform: %s ' .
                            '(when forceInsertAllowance)',
                            $productId,
                            $billingPlatform
                        )
                    );
                }

                // detail値はallowedToPurchase処理時のログで出力されます
                return new NullAllowanceCallbackEntity(
                    $osPlatform,
                    $productId,
                    $oprProduct->getId(),
                    $deviceId,
                    // Json形式でdetailにログ出力
                    [
                        'detail' => 'Auto insert because allowance is null.',
                    ]
                );
            }
        );

        // 購入許可情報がない場合はエラー
        if (is_null($usrStoreAllowanceEntity)) {
            throw new GameException(
                ErrorCode::BILLING_ALLOWANCE_FAILED,
                sprintf(
                    'Allowance not found even after attempting auto-insertion. billing_platform: %s, product_id: %s',
                    $billingPlatform,
                    $productId,
                ),
            );
        }

        return $usrStoreAllowanceEntity;
    }
}

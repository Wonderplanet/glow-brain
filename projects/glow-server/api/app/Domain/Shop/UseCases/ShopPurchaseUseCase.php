<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Common\Utils\PlatformUtil;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\ShopPurchasedTrigger;
use App\Domain\Resource\Entities\Rewards\ShopDiamondReward;
use App\Domain\Resource\Entities\Rewards\ShopPackContentReward;
use App\Domain\Resource\Entities\Rewards\ShopPassReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Repositories\MstPackRepository;
use App\Domain\Resource\Mst\Repositories\MstShopPassRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Repositories\UsrShopPassRepository;
use App\Domain\Shop\Repositories\UsrStoreProductRepository;
use App\Domain\Shop\Services\AppShopService;
use App\Domain\Shop\Services\ShopBillingService;
use App\Domain\Shop\Services\ShopPurchaseHistoryService;
use App\Domain\Shop\Services\ShopService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ShopPurchaseResultData;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingDuplicateReceiptException;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingEndTransactionException;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;

class ShopPurchaseUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        // Mst Repository
        private MstPackRepository $mstPackRepository,
        private MstShopPassRepository $mstShopPassRepository,
        // Usr Repository
        private UsrShopPassRepository $usrShopPassRepository,
        private UsrStoreProductRepository $usrStoreProductRepository,
        // Service
        private ShopService $shopService,
        private ShopBillingService $shopBillingService,
        private AppShopService $appShopService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private ShopPurchaseHistoryService $shopPurchaseHistoryService,
        // 課金基盤
        private AppCurrencyDelegator $appCurrencyDelegator,
        // Delegator
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
        private BillingDelegator $billingDelegator,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param int $platform
     * @param string $billingPlatform
     * @param string $oprProductId
     * @param string $purchasePrice
     * @param string $rawPriceString
     * @param string $currencyCode
     * @param string $receipt
     * @param string $language
     * @return ShopPurchaseResultData
     * @throws GameException
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        string $billingPlatform,
        string $oprProductId,
        string $purchasePrice,
        string $rawPriceString,
        string $currencyCode,
        string $receipt,
        string $language,
    ): ShopPurchaseResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // オファーコード商品の場合、価格を0円に強制
        // オファーコードにより実際の支払額が0円となる商品について、
        // 未成年ユーザーの累積課金額やBanKへの売上データが正しく記録されるようにする
        if ($this->appShopService->isOfferCodeProduct($oprProductId)) {
            $purchasePrice = '0';
            $rawPriceString = '¥0';
        }

        /**
         * 商品のマスタ取得。期間チェックはなし。
         *
         * 期間チェックなど、購入可能かどうかは、allowance処理で行い、ここでは課金処理のみを行う。
         * ただし、マスタデータが存在しない場合は、正常に処理を続行できないため、エラーとしている。
         */
        $oprProduct = $this->appShopService->getValidOprProductById($oprProductId, $now, false);
        $mstProductId = $oprProduct->getMstStoreProductId();
        $mstStoreProduct = $this->appCurrencyDelegator->getMstStoreProductById($mstProductId);
        if (is_null($mstStoreProduct)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "'mst_store_products record is not found. (id: $mstProductId})",
            );
        }

        $osPlatform = PlatformUtil::convertPlatformToCurrencyOsPlatform($platform);
        $productSubId = $oprProduct->getId();
        $productId = $mstStoreProduct->getProductIdByBillingPlatform($billingPlatform);
        $deviceId = $this->appShopService->getDeviceId($usrUserId);

        /**
         * レシート検証
         */

        // レシートの確認
        // 課金基盤ライブラリのExceptionが出たら上位のHandlerでGameExceptionに変換される
        // 処理済みの場合はallowanceも消えているため、allowanceの取得より先にレシートのチェックを行う。
        try {
            $storeReceipt = $this->billingDelegator->verifyReceipt($billingPlatform, $productId, $receipt);
        } catch (WpBillingDuplicateReceiptException $e) {
            throw new GameException(ErrorCode::BILLING_VERIFY_RECEIPT_DUPLICATE_RECEIPT, $e->getMessage(), $e);
        } catch (WpBillingException $e) {
            // レシートの検証に失敗
            throw new GameException(ErrorCode::BILLING_VERIFY_RECEIPT_INVALID_RECEIPT, $e->getMessage(), $e);
        }

        /**
         * 購入許可情報の取得
         */
        $usrStoreAllowance = $this->transaction(
            function () use (
                $usrUserId,
                $billingPlatform,
                $productId,
                $storeReceipt,
                $osPlatform,
                $deviceId,
            ) {
                return $this->shopBillingService->getOrForceInsertAllowance(
                    $usrUserId,
                    $billingPlatform,
                    $productId,
                    $storeReceipt,
                    $osPlatform,
                    $deviceId,
                );
            }
        );

        $oprProductName = $productSubId;
        $trigger = new ShopPurchasedTrigger(
            $productSubId,
            $oprProductName,
            $productId,
            $billingPlatform,
            $mstProductId,
            $language,
            $currencyCode,
            $purchasePrice,
        );

        /**
         * 決済完了していてレシート検証も通っているが、購入できないはずの商品だったら、エラーまたは購入トランザクションを強制的に閉じる
         */
        try {
            // 購入可能かチェック
            $this->shopBillingService->validatePurchase($usrUserId, $now, $oprProduct);
            // 購入金額による年齢確認
            // リストアになってからアカウント切り替えて年齢入力していない場合は、ここでエラーになる
            $this->appShopService->updateAndValidateUsrStoreInfoForPurchase(
                $usrUserId,
                $now,
                $purchasePrice,
                $currencyCode,
            );
        } catch (GameException $e) {
            /**
             * 購入回数上限を超えていて購入できないはずなので、トランザクションを強制終了する
             */
            if ($e->getCode() === ErrorCode::SHOP_TRADE_COUNT_LIMIT) {
                $this->transaction(
                    function () use (
                        $usrStoreAllowance,
                        $purchasePrice,
                        $rawPriceString,
                        $currencyCode,
                        $storeReceipt,
                        $trigger,
                        $oprProductName,
                    ) {
                        try {
                            // トランザクションを終了する際にpurchasedで処理する予定のforceClosePurchaseを実行
                            $this->billingDelegator->forceClosePurchase(
                                $usrStoreAllowance->usr_user_id,
                                $usrStoreAllowance->os_platform,
                                $usrStoreAllowance->billing_platform,
                                $usrStoreAllowance->device_id,
                                $usrStoreAllowance,
                                $purchasePrice,
                                $rawPriceString,
                                $currencyCode,
                                $storeReceipt,
                                $trigger,
                                $oprProductName,
                            );
                        } catch (WpBillingEndTransactionException $e) {
                            /**
                             * forceClosePurchaseでは、想定通り処理が進むと、WpBillingEndTransactionExceptionが投げられる。
                             * 通常trandactionに指定したcallbackで例外が投げられると、DBロールバックすることになるが、
                             * forceClosePurchaseでの変更をロールバックしないために、ここでは何もしない。
                             *
                             * larave-wp-frameworkからコメントコピー:
                             * 必要に応じて後処理
                             * SEEDではServerErrorCode::BillingTransactionEndPurchaseLimit->valueを返却したいため何もしない
                             * WpBillingEndTransactionException以外のエラーも対象になるためWpBillingExceptionを指定しないこと
                             */
                        }
                    }
                );
                // トランザクション強制終了のデータ更新を完了したのちに、エラーを投げる
                throw new GameException(
                    ErrorCode::BILLING_TRANSACTION_END_PURCHASE_LIMIT,
                    $e->getMessage(),
                    $e,
                );
            } else {
                // その他の例外は、トランザクション強制終了は行わず、そのまま投げる
                throw $e;
            }
        }

        /**
         * 商品タイプごとの配布処理を用意
         */
        $usrShopPass = null;
        $shopPassRewards = collect();
        if ($oprProduct->getProductType() === ProductType::PASS->value) {
            // ショップパスの場合
            $mstShopPass = $this->mstShopPassRepository->getByOprProductId($oprProduct->getId(), true);
            $purchaseOperationCallback
                = function () use ($usrUserId, $oprProduct, $mstShopPass, $now, &$shopPassRewards, &$usrShopPass) {
                // ユーザーのショップパスをトレードする
                    $shopPassRewards = $this->shopService->tradeShopPass(
                        $usrUserId,
                        $oprProduct,
                        $mstShopPass,
                        $now,
                    );

                    $usrShopPass = $this->usrShopPassRepository->getActivePass(
                        $usrUserId,
                        $mstShopPass->getId(),
                        $now,
                    );
                };
        } elseif ($oprProduct->getProductType() === ProductType::PACK->value) {
            // パックの場合
            $mstPack = $this->mstPackRepository->getByProductSubId($oprProduct->getId(), true);
            $purchaseOperationCallback = function () use ($usrUserId, $oprProduct, $mstPack, $platform, $now) {
                $this->shopService->purchasePack(
                    $usrUserId,
                    $oprProduct,
                    $mstPack,
                    $platform,
                    $now,
                );
            };
        } elseif ($oprProduct->getProductType() === ProductType::DIAMOND->value) {
            // ダイアモンドの場合
            $purchaseOperationCallback = function () use ($usrUserId, $oprProduct, $now) {
                $this->shopService->purchaseDiamond(
                    $usrUserId,
                    $oprProduct,
                    $now,
                );
            };
        } else {
            // その他のプロダクトタイプは未対応
            throw new GameException(
                ErrorCode::SHOP_PURCHASE_PRODUCT_TYPE_NOT_SUPPORTED,
                "Product type {$oprProduct->getProductType()} is not supported.",
            );
        }

        /**
         * 課金処理を実行
         */
        $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $usrStoreAllowance,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $storeReceipt,
            $trigger,
            $oprProductName,
            $purchaseOperationCallback,
        ) {
            // 課金基盤を使って購入処理を実行し、成功したら、コールバックで指定したプロジェクト固有のパック購入処理を実行する
            try {
                // GLOWではvipポイントの仕様はないので、0にしておく。
                $vipPoint = 0;

                $this->billingDelegator->purchased(
                    $usrUserId,
                    $osPlatform,
                    $billingPlatform,
                    $deviceId,
                    $usrStoreAllowance,
                    $purchasePrice,
                    $rawPriceString,
                    $vipPoint,
                    $currencyCode,
                    $storeReceipt,
                    $trigger,
                    $oprProductName,
                    // 課金処理成功後にプロジェクト固有の処理をコールバックとして指定し実行する
                    $purchaseOperationCallback,
                );
            } catch (WpBillingException $e) {
                throw new GameException(ErrorCode::UNKNOWN_ERROR, $e->getMessage(), $e);
            }
        });


        /**
         * レスポンス整形
         */
        $usrStoreProduct = $this->usrStoreProductRepository->get($user->id, $oprProductId);
        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);

        // 実際に配布されたリワードを取得
        $sentRewards = collect();
        $sentRewards = $sentRewards->merge($this->rewardDelegator->getSentRewards(ShopPackContentReward::class));
        $sentRewards = $sentRewards->merge($this->rewardDelegator->getSentRewards(ShopPassReward::class));
        $sentRewards = $sentRewards->merge($this->rewardDelegator->getSentRewards(ShopDiamondReward::class));

        // 商品タイプごとにレスポンス調整

        // 有償プリズム
        // 配布された有償ダイヤの報酬情報をレスポンスに直接追加する
        // - 有償プリズム購入では、実際のリソース付与が課金処理内で行われるため、RewardDelegatorから配布済み情報を取得できない
        // - 有償プリズムには、報酬変換仕様がなく、マスタ登録されている報酬情報から変更はないため、マスタから取得してもズレることはない
        // - 所持上限超過する購入の場合は、エラーが出るため、配布量が減ることもない
        if ($oprProduct->getProductType() === ProductType::DIAMOND->value) {
            $diamondReward = new ShopDiamondReward(
                RewardType::PAID_DIAMOND->value,
                $oprProduct->getId(),
                $oprProduct->getPaidAmount()
            );
            $sentRewards->push($diamondReward);

            $this->shopPurchaseHistoryService->setCurrencyPurchaseHistory(
                $usrUserId,
                $billingPlatform,
                $purchasePrice,
                $oprProduct->getPaidAmount(),
                $currencyCode,
                $now,
            );
        }

        // ユーザーのストア情報を取得
        $usrStoreInfo = $this->billingDelegator->getStoreInfo((string) $user->getId());

        return new ShopPurchaseResultData(
            $usrStoreProduct,
            $usrShopPass ?? null,
            $shopPassRewards,
            $sentRewards,
            $this->makeUsrParameterData($usrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->shopService->getUsrTradePackList($usrUserId, $now),
            $usrStoreInfo,
        );
    }
}

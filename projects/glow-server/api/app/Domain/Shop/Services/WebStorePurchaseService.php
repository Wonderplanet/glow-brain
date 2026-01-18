<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Entities\Rewards\ShopPackContentReward;
use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Resource\Mst\Repositories\MstPackContentRepository;
use App\Domain\Resource\Mst\Repositories\MstPackRepository;
use App\Domain\Resource\Mst\Repositories\MstStoreProductI18nRepository;
use App\Domain\Resource\Mst\Repositories\MstStoreProductRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Entities\WebStoreItemEntity;
use App\Domain\Shop\Repositories\UsrStoreProductRepository;
use App\Domain\Shop\Repositories\UsrWebstoreInfoRepository;
use App\Domain\Shop\Repositories\UsrWebstoreTransactionRepository;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;

/**
 * WebStore購入処理サービス
 *
 * WebStoreからの購入通知を処理し、以下の責務を担当：
 * - べき等性チェック（重複注文の検出）
 * - 購入アイテムのフィルタリング（virtual_good抽出）
 * - 購入検証（商品存在確認、購入回数制限チェック）
 * - アイテム付与処理
 * - 購入履歴保存
 * - Adjustイベント送信
 * - トランザクション状態更新
 */
class WebStorePurchaseService
{
    public function __construct(
        private readonly MstStoreProductRepository $mstStoreProductRepository,
        private readonly MstStoreProductI18nRepository $mstStoreProductI18nRepository,
        private readonly MstPackRepository $mstPackRepository,
        private readonly MstPackContentRepository $mstPackContentRepository,
        private readonly AppCurrencyDelegator $appCurrencyDelegator,
        private readonly UsrStoreProductRepository $usrStoreProductRepository,
        private readonly UsrWebstoreTransactionRepository $usrWebstoreTransactionRepository,
        private readonly UsrWebstoreInfoRepository $usrWebstoreInfoRepository,
        private readonly UsrUserProfileRepository $usrUserProfileRepository,
        private readonly BillingDelegator $billingDelegator,
        private readonly RewardDelegator $rewardDelegator,
        private readonly AdjustEventService $adjustEventService,
        private readonly ShopBillingService $shopBillingService,
    ) {
    }

    /**
     * べき等性チェック（重複注文の検出）
     *
     * @param int $orderId 注文ID
     * @return bool true: 重複（既に処理済み）, false: 新規リクエスト
     */
    public function isDuplicateOrder(int $orderId): bool
    {
        return $this->billingDelegator->existsByOrderId($orderId);
    }

    /**
     * アイテム配列をEntityコレクションに変換
     *
     * @param array<string, mixed> $items アイテムデータ配列
     * @return Collection<int, WebStoreItemEntity>
     */
    public function convertItemsToEntities(array $items): Collection
    {
        $collection = collect();
        foreach ($items as $item) {
            $collection->push(WebStoreItemEntity::fromArray($item));
        }
        return $collection;
    }

    /**
     * items配列からtype=virtual_goodのみをフィルタ
     *
     * @param Collection $webStoreItems 購入アイテム一覧
     * @return Collection<int, WebStoreItemEntity> フィルタ後のアイテムコレクション
     * @throws GameException virtual_goodが存在しない場合
     */
    public function filterVirtualGoodItems(Collection $webStoreItems): Collection
    {
        $virtualGoodItems = $webStoreItems->filter(fn(WebStoreItemEntity $item) => $item->isVirtualGood());

        if ($virtualGoodItems->isEmpty()) {
            throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
        }

        return $virtualGoodItems->values();
    }

    /**
     * 購入アイテムの検証（商品存在確認と購入回数制限チェック）
     *
     * @param Collection<int, WebStoreItemEntity> $virtualGoodItems virtual_goodアイテム一覧
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在日時
     * @return void
     * @throws GameException 商品が存在しない、または購入制限に引っかかる場合
     */
    public function validatePurchaseItems(Collection $virtualGoodItems, string $usrUserId, CarbonImmutable $now): void
    {
        // 1. 全SKUを抽出
        $skus = $virtualGoodItems->map(fn(WebStoreItemEntity $item) => $item->getSku())->all();

        // 2. SKUからmst_store_productsを一括取得
        $mstStoreProducts = $this->mstStoreProductRepository->getByProductIdWebstores($skus);

        // 3. mst_store_product_idを抽出
        $mstProductIds = $mstStoreProducts->map(fn($entity) => $entity->getId())->all();

        // 4. opr_productsを一括取得
        $oprProducts = $this->appCurrencyDelegator->getOprProductsByMstProductIds($mstProductIds);

        // 5. 各アイテムをバリデーション
        foreach ($virtualGoodItems as $item) {
            $sku = $item->getSku();

            // mst_store_productの存在確認
            if (!$mstStoreProducts->has($sku)) {
                throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
            }

            $mstStoreProduct = $mstStoreProducts->get($sku);

            // opr_productの存在確認
            if (!$oprProducts->has($mstStoreProduct->getId())) {
                throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
            }

            $oprProduct = $oprProducts->get($mstStoreProduct->getId());

            // 購入回数制限チェック
            $this->shopBillingService->validatePurchase($usrUserId, $now, $oprProduct);
        }
    }

    /**
     * 注文処理を実行
     *
     * @param string $usrUserId ユーザーID
     * @param int $orderId 注文ID
     * @param string|null $invoiceId 請求ID
     * @param string|null $currencyCode 通貨コード
     * @param int $orderAmount 注文総額
     * @param string $orderMode 決済モード（'default' or 'sandbox'）
     * @param Collection $items 購入アイテム一覧
     * @param string $transactionId トランザクションID
     * @param string|null $clientIp クライアントIPアドレス
     * @param int $platform プラットフォーム
     * @param CarbonImmutable $now 現在日時
     * @return void
     * @throws GameException
     */
    public function processOrder(
        string $usrUserId,
        int $orderId,
        ?string $invoiceId,
        ?string $currencyCode,
        int $orderAmount,
        string $orderMode,
        Collection $items,
        string $transactionId,
        ?string $clientIp,
        int $platform,
        CarbonImmutable $now
    ): void {
        // 1. virtual_goodアイテムのフィルタリング
        $virtualGoodItems = $this->filterVirtualGoodItems($items);

        $isSandbox = $orderMode === WebStoreConstant::SANDBOX;

        // 2. 各アイテムを付与
        foreach ($virtualGoodItems as $item) {
            $sku = $item->getSku();

            // SKUからmst_store_productsを検索
            $mstStoreProduct = $this->mstStoreProductRepository->findByProductIdWebstore($sku);
            if (is_null($mstStoreProduct)) {
                Log::error('WebStore product not found', ['sku' => $sku]);
                throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
            }

            // mst_store_products.idからopr_productsを取得
            $oprProduct = $this->appCurrencyDelegator->getOprProductByMstProductId($mstStoreProduct->getId());
            if (is_null($oprProduct)) {
                Log::error('OPR product not found', ['mst_store_product_id' => $mstStoreProduct->getId()]);
                throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
            }

            // 商品タイプに応じたアイテム付与（購入履歴保存を含む）
            // 注文全体の金額（order.amount）を使用（仕様書 p.19参照）
            $this->grantItems(
                $usrUserId,
                $mstStoreProduct,
                $oprProduct,
                $orderAmount,
                $currencyCode,
                $orderId,
                $invoiceId,
                $transactionId,
                $isSandbox,
                $platform,
                $now
            );

            // usr_store_productsの購入回数をカウントアップ
            $this->usrStoreProductRepository->incrementPurchaseCount($usrUserId, $oprProduct->getId(), $now);
        }

        // 3. Adjustイベント送信
        // usr_user_profileからmy_idを取得（external_device_idとして使用）
        $usrUserProfile = $this->usrUserProfileRepository->findByUsrUserId($usrUserId);

        // usr_webstore_infoからos_platformとad_idを取得
        $usrWebstoreInfo = $this->usrWebstoreInfoRepository->get($usrUserId);
        $osPlatform = $usrWebstoreInfo->getOsPlatform();
        $adId = $usrWebstoreInfo->getAdId();

        $adjustStatus = $this->adjustEventService->sendPurchaseEvent(
            $usrUserProfile->getMyId(),
            $currencyCode,
            $orderAmount,
            $clientIp,
            $osPlatform,
            $adId,
            $now,
            $isSandbox
        );

        // 4. usr_webstore_transactionsを'completed'に更新し、order_idとAdjustステータスを保存
        $transaction = $this->usrWebstoreTransactionRepository->findByTransactionId($transactionId);
        if ($transaction) {
            $transaction->setOrderId($orderId);
            $transaction->setStatus(WebStoreConstant::TRANSACTION_STATUS_COMPLETED);
            $transaction->setAdjustStatus($adjustStatus);
            $this->usrWebstoreTransactionRepository->syncModel($transaction);
        }
    }

    /**
     * アイテム付与処理
     *
     * @param string $usrUserId ユーザーID
     * @param MstStoreProductEntity $mstStoreProduct マスター商品
     * @param OprProductEntity $oprProduct OPR商品情報
     * @param int $amount 金額
     * @param string|null $currencyCode 通貨コード
     * @param int $orderId 注文ID
     * @param string|null $invoiceId 請求ID
     * @param string $transactionId トランザクションID
     * @param bool $isSandbox サンドボックスモードか
     * @param int $platform プラットフォーム
     * @param CarbonImmutable $now 現在日時
     * @return void
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    private function grantItems(
        string $usrUserId,
        MstStoreProductEntity $mstStoreProduct,
        OprProductEntity $oprProduct,
        int $amount,
        ?string $currencyCode,
        int $orderId,
        ?string $invoiceId,
        string $transactionId,
        bool $isSandbox,
        int $platform,
        CarbonImmutable $now
    ): void {
        if (!$oprProduct->isDiamond() && !$oprProduct->isPack()) {
            // WebStoreで対応していない商品タイプ（ダイヤモンドとパック以外）
            Log::error('WebStore does not support this product type', [
                'product_type' => $oprProduct->getProductType(),
                'opr_product_id' => $oprProduct->getId(),
                'mst_store_product_id' => $mstStoreProduct->getId(),
                'sku' => $mstStoreProduct->getProductIdWebstore(),
            ]);
            throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
        }

        // 購入履歴保存を含む購入処理を実行（共通処理）
        $this->billingDelegator->purchasedForWebStore(
            $usrUserId,
            $oprProduct->getId(),
            $oprProduct->getPaidAmount(),
            $currencyCode ?? 'JPY',
            $orderId,
            $invoiceId,
            $transactionId,
            $mstStoreProduct->getId(),
            $mstStoreProduct->getProductIdWebstore() ?? '',
            (string) $amount,
            0, // VIPポイントは0
            $isSandbox
        );

        // パック商品の場合は、パック内容を報酬として付与
        if ($oprProduct->isPack()) {
            $mstPack = $this->mstPackRepository->getByProductSubId($oprProduct->getId(), true);

            // パック内容を取得して報酬として付与
            $rewards = collect();
            $mstPackContents = $this->mstPackContentRepository->getByMstPackId($mstPack->getId());

            foreach ($mstPackContents as $mstPackContent) {
                /** @var \App\Domain\Resource\Mst\Entities\MstPackContentEntity $mstPackContent */
                $rewards->push(new ShopPackContentReward(
                    $mstPackContent->getResourceType(),
                    $mstPackContent->getResourceId(),
                    $mstPackContent->getResourceAmount(),
                    $mstPackContent->getMstPackId(),
                ));
            }
            $this->rewardDelegator->addRewards($rewards);
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
        }
    }

    /**
     * アイテム情報から有料商品かどうかを判定
     *
     * W2（決済事前確認）では金額情報（order.amount）が送られてこないため、
     * mst_store_products_i18n.price_webstoreから有料/無料を判定する。
     * 仕様書p.8, p.20より、無料アイテムもW2（決済事前確認）に到達する。
     *
     * @param Collection<int, WebStoreItemEntity> $webStoreItemEntities アイテムコレクション
     * @return bool true: 有料, false: 無料
     */
    public function isPaidOrderFromItems(Collection $webStoreItemEntities): bool
    {
        // 1. SKUのリストを取得
        $skus = $webStoreItemEntities->map(fn(WebStoreItemEntity $item) => $item->getSku())->all();

        // 2. SKUからmst_store_productsを取得
        $mstStoreProducts = $this->mstStoreProductRepository->getByProductIdWebstores($skus);

        // 3. mst_store_product_idのリストを取得
        $mstStoreProductIds = $mstStoreProducts->map(fn($entity) => $entity->getId());

        // 4. mst_store_product_idから価格情報を取得
        $priceInfos = $this->mstStoreProductI18nRepository->getByMstStoreProductIds($mstStoreProductIds);

        // 5. いずれかの商品でprice_webstore > 0なら有料
        foreach ($priceInfos as $priceInfo) {
            $priceWebstore = $priceInfo->getPriceWebstore();
            if ($priceWebstore !== null && $priceWebstore > 0) {
                return true;
            }
        }

        // 全て0円またはNULLなら無料
        return false;
    }
}

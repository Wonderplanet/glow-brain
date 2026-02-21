<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\WebStorePurchasedTrigger;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Resource\Mst\Repositories\MstPackContentRepository;
use App\Domain\Resource\Mst\Repositories\MstPackRepository;
use App\Domain\Resource\Mst\Repositories\MstStoreProductI18nRepository;
use App\Domain\Resource\Mst\Repositories\MstStoreProductRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Entities\WebStoreItemEntity;
use App\Domain\Shop\Repositories\UsrWebstoreInfoRepository;
use App\Domain\Shop\Repositories\UsrWebstoreTransactionRepository;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddFreeCurrencyOverByMaxException;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddPaidCurrencyOverByMaxException;

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
        // repositories
        private readonly MstStoreProductRepository $mstStoreProductRepository,
        private readonly MstStoreProductI18nRepository $mstStoreProductI18nRepository,
        private readonly MstPackRepository $mstPackRepository,
        private readonly MstPackContentRepository $mstPackContentRepository,
        private readonly UsrWebstoreTransactionRepository $usrWebstoreTransactionRepository,
        private readonly UsrWebstoreInfoRepository $usrWebstoreInfoRepository,
        // services
        private readonly AdjustEventService $adjustEventService,
        private readonly ShopBillingService $shopBillingService,
        private readonly ShopService $shopService,
        private readonly MstConfigService $mstConfigService,
        // delegators
        private readonly AppCurrencyDelegator $appCurrencyDelegator,
        private readonly BillingDelegator $billingDelegator,
        private readonly UserDelegator $userDelegator,
        private readonly ItemDelegator $itemDelegator,
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
     * 購入アイテムの検証（商品存在確認、購入回数制限チェック、パック内アイテム所持上限チェック）
     *
     * @param Collection<int, WebStoreItemEntity> $virtualGoodItems virtual_goodアイテム一覧
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在日時
     * @return void
     * @throws GameException 商品が存在しない、購入制限に引っかかる、またはアイテム所持上限を超える場合
     */
    public function validatePurchaseItems(Collection $virtualGoodItems, string $usrUserId, CarbonImmutable $now): void
    {
        // 1. 全SKUを抽出
        $skus = $virtualGoodItems->map(fn(WebStoreItemEntity $item) => $item->getSku())->all();

        // 2. SKUからmst_store_productsを一括取得
        $mstStoreProducts = $this->mstStoreProductRepository->getByProductIdWebstores($skus);

        // 3. mst_store_product_idを抽出
        $mstProductIds = $mstStoreProducts->map(fn($entity) => $entity->getId())->all();

        // 4. opr_productsを一括取得（販売期間内のもののみ）
        $oprProducts = $this->appCurrencyDelegator->getOprProductsByMstProductIds($mstProductIds, $now);

        // 5. 各アイテムをバリデーション
        foreach ($virtualGoodItems as $item) {
            $sku = $item->getSku();

            // mst_store_productの存在確認
            if (!$mstStoreProducts->has($sku)) {
                throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
            }

            $mstStoreProduct = $mstStoreProducts->get($sku);

            // opr_productの存在確認（販売期間外の場合もここでエラーになる）
            if (!$oprProducts->has($mstStoreProduct->getId())) {
                throw new GameException(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);
            }

            $oprProduct = $oprProducts->get($mstStoreProduct->getId());

            // 購入回数制限チェック
            $this->shopBillingService->validatePurchase($usrUserId, $now, $oprProduct);

            // パック商品の場合、リソースの所持上限チェック
            if ($oprProduct->isPack()) {
                $this->validatePackResourcePossessionLimit($usrUserId, $oprProduct->getId());
            }

            // 有償通貨の所持上限チェック（パック商品・ダイヤモンド商品共通）
            $this->validatePaidDiamondLimit($usrUserId, $oprProduct->getPaidAmount());
        }
    }

    /**
     * パック内リソース（コイン・アイテム・有償通貨）の所持上限チェック
     *
     * @param string $usrUserId ユーザーID
     * @param string $productSubId 商品ID (opr_product_id)
     * @return void
     * @throws GameException リソースの所持上限を超える場合
     */
    private function validatePackResourcePossessionLimit(string $usrUserId, string $productSubId): void
    {
        // パック内容を取得
        $mstPack = $this->mstPackRepository->getByProductSubId($productSubId);
        if (is_null($mstPack)) {
            // パックが見つからない場合は何もしない（商品存在チェックは別で行われている）
            return;
        }

        $mstPackContents = $this->mstPackContentRepository->getByMstPackId($mstPack->getId());
        if ($mstPackContents->isEmpty()) {
            // パック内容が空の場合は何もしない
            return;
        }

        // 1. コインの上限チェック
        $this->validateCoinLimit($usrUserId, $mstPackContents);

        // 2. アイテムの上限チェック
        $this->validateItemLimit($usrUserId, $mstPackContents);

        // 3. 無償通貨の上限チェック
        $this->validateFreeDiamondLimit($usrUserId, $mstPackContents);
    }

    /**
     * パック内コインの所持上限チェック
     *
     * @param string $usrUserId ユーザーID
     * @param Collection $mstPackContents パック内容
     * @return void
     * @throws GameException コイン所持上限を超える場合
     */
    private function validateCoinLimit(string $usrUserId, Collection $mstPackContents): void
    {
        // コインタイプのリソースのみをフィルタリング
        $coinContents = $mstPackContents->filter(function ($content) {
            return $content->getResourceType() === RewardType::COIN->value;
        });

        if ($coinContents->isEmpty()) {
            return;
        }

        // ユーザーの現在のコイン所持数を取得
        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);
        $currentCoin = $usrUserParameter->getCoin();

        // コインの所持上限を取得
        $maxCoin = $this->mstConfigService->getUserCoinMaxAmount();

        // パック内の合計コイン数を計算
        $totalAddCoin = $coinContents->sum(function ($content) {
            return $content->getResourceAmount();
        });

        $afterAmount = $currentCoin + $totalAddCoin;

        if ($afterAmount > $maxCoin) {
            throw new GameException(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);
        }
    }

    /**
     * パック内アイテムの所持上限チェック
     *
     * @param string $usrUserId ユーザーID
     * @param Collection $mstPackContents パック内容
     * @return void
     * @throws GameException アイテム所持上限を超える場合
     */
    private function validateItemLimit(string $usrUserId, Collection $mstPackContents): void
    {
        // アイテムタイプのリソースのみをフィルタリング
        $itemContents = $mstPackContents->filter(function ($content) {
            return $content->getResourceType() === RewardType::ITEM->value;
        });

        if ($itemContents->isEmpty()) {
            return;
        }

        // アイテムID一覧を取得
        $mstItemIds = $itemContents->map(function ($content) {
            return $content->getResourceId();
        });

        // ユーザーの現在のアイテム所持状況を取得
        $usrItems = $this->itemDelegator->getListByMstItemIds($usrUserId, $mstItemIds);

        // アイテムの所持上限を取得
        $maxAmount = $this->mstConfigService->getUserItemMaxAmount();

        // 各アイテムについて所持上限チェック
        foreach ($itemContents as $mstPackContent) {
            /** @var \App\Domain\Resource\Mst\Entities\MstPackContentEntity $mstPackContent */
            $mstItemId = $mstPackContent->getResourceId();
            $addAmount = $mstPackContent->getResourceAmount();

            /** @var \App\Domain\Item\Models\UsrItemInterface|null $usrItem */
            $usrItem = $usrItems->get($mstItemId);
            $currentAmount = $usrItem ? $usrItem->getAmount() : 0;

            $afterAmount = $currentAmount + $addAmount;

            if ($afterAmount > $maxAmount) {
                throw new GameException(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);
            }
        }
    }

    /**
     * パック内無償通貨の所持上限チェック
     *
     * @param string $usrUserId ユーザーID
     * @param Collection $mstPackContents パック内容
     * @return void
     * @throws GameException 無償通貨所持上限を超える場合
     */
    private function validateFreeDiamondLimit(string $usrUserId, Collection $mstPackContents): void
    {
        // 無償通貨タイプのリソースのみをフィルタリング
        $freeDiamondContents = $mstPackContents->filter(function ($content) {
            return $content->getResourceType() === RewardType::FREE_DIAMOND->value;
        });

        if ($freeDiamondContents->isEmpty()) {
            return;
        }

        // パック内の合計無償通貨数を計算
        $totalAddFreeDiamond = $freeDiamondContents->sum(function ($content) {
            return $content->getResourceAmount();
        });

        // 追加する無償通貨がない場合はチェック不要
        if ($totalAddFreeDiamond <= 0) {
            return;
        }

        // 課金基盤のvalidateAddCurrencyで無償通貨の上限チェック（上限超過時に例外が投げられる）
        try {
            $this->appCurrencyDelegator->validateAddCurrency($usrUserId, 0, $totalAddFreeDiamond);
        } catch (WpCurrencyAddCurrencyOverByMaxException | WpCurrencyAddFreeCurrencyOverByMaxException $e) {
            throw new GameException(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);
        }
    }

    /**
     * 有償通貨の所持上限チェック
     *
     * @param string $usrUserId ユーザーID
     * @param int $paidAmount 追加する有償通貨量
     * @return void
     * @throws GameException 有償通貨所持上限を超える場合
     */
    private function validatePaidDiamondLimit(string $usrUserId, int $paidAmount): void
    {
        // 追加する有償通貨がない場合はチェック不要
        if ($paidAmount <= 0) {
            return;
        }

        // 課金基盤のvalidateAddCurrencyで有償通貨の上限チェック（上限超過時に例外が投げられる）
        try {
            $this->appCurrencyDelegator->validateAddCurrency($usrUserId, $paidAmount, 0);
        } catch (WpCurrencyAddCurrencyOverByMaxException | WpCurrencyAddPaidCurrencyOverByMaxException $e) {
            throw new GameException(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);
        }
    }

    /**
     * 注文処理を実行
     *
     * @param string $usrUserId ユーザーID
     * @param int $orderId 注文ID
     * @param string|null $invoiceId 請求ID
     * @param string|null $currencyCode 通貨コード
     * @param string $orderMode 決済モード（'default' or 'sandbox'）
     * @param Collection $items 購入アイテム一覧
     * @param string $transactionId トランザクションID
     * @param int $platform プラットフォーム
     * @param CarbonImmutable $now 現在日時
     * @return Collection<string> 付与した商品のproduct_sub_id一覧
     * @throws GameException
     */
    public function processOrder(
        string $usrUserId,
        int $orderId,
        ?string $invoiceId,
        ?string $currencyCode,
        string $orderMode,
        Collection $items,
        string $transactionId,
        int $platform,
        CarbonImmutable $now
    ): Collection {
        // 1. virtual_goodアイテムのフィルタリング
        $virtualGoodItems = $this->filterVirtualGoodItems($items);

        $isSandbox = $orderMode === WebStoreConstant::SANDBOX;

        // 2. 各アイテムを付与
        $productSubIds = collect();
        $itemIndex = 1;
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
            // アイテムごとの金額を使用（W4: price.amount, W5: amount）
            $itemAmount = $item->getAmount();

            // 複数アイテム対応: receipt_unique_idに連番を付与
            // 1つ目: "{order_id}", 2つ目以降: "{order_id}_{連番}"
            $receiptUniqueId = $itemIndex === 1 ? (string)$orderId : "{$orderId}_{$itemIndex}";

            $loggingProductSubName = $oprProduct->getId();
            $this->grantItems(
                $usrUserId,
                $mstStoreProduct,
                $oprProduct,
                $itemAmount,
                $currencyCode,
                $receiptUniqueId,
                $orderId,
                $invoiceId,
                $transactionId,
                $isSandbox,
                $platform,
                $now,
                $loggingProductSubName
            );

            // アプリ通知用にproduct_sub_idを収集
            $productSubIds->push($oprProduct->getId());
            $itemIndex++;
        }

        // 3. usr_webstore_transactionsを'completed'に更新し、order_idを保存
        $transaction = $this->usrWebstoreTransactionRepository->findByTransactionId($transactionId);
        if ($transaction) {
            $transaction->setOrderId($orderId);
            $transaction->setStatus(WebStoreConstant::TRANSACTION_STATUS_COMPLETED);
            $this->usrWebstoreTransactionRepository->syncModel($transaction);
        }
        return $productSubIds;
    }

    public function sendAdjustEvent(
        string $usrUserId,
        ?string $currencyCode,
        int $orderAmount,
        string $orderMode,
        ?string $clientIp,
        string $transactionId,
        CarbonImmutable $now
    ): void {
        // usr_user_profileからmy_idを取得（external_device_idとして使用）
        $usrUserProfile = $this->userDelegator->getUsrUserProfileByUsrUserId($usrUserId);

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
            $orderMode === WebStoreConstant::SANDBOX
        );
        $this->usrWebstoreTransactionRepository->updateAdjustStatus($transactionId, $adjustStatus);
    }

    /**
     * アイテム付与処理
     *
     * @param string $usrUserId ユーザーID
     * @param MstStoreProductEntity $mstStoreProduct マスター商品
     * @param OprProductEntity $oprProduct OPR商品情報
     * @param int $amount 金額
     * @param string|null $currencyCode 通貨コード
     * @param string $receiptUniqueId レシートユニークID
     * @param int $orderId 注文ID
     * @param string|null $invoiceId 請求ID
     * @param string $transactionId トランザクションID
     * @param bool $isSandbox サンドボックスモードか
     * @param int $platform プラットフォーム
     * @param CarbonImmutable $now 現在日時
     * @param string $loggingProductSubName ログに記録する商品名
     * @return void
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    private function grantItems(
        string $usrUserId,
        MstStoreProductEntity $mstStoreProduct,
        OprProductEntity $oprProduct,
        int $amount,
        ?string $currencyCode,
        string $receiptUniqueId,
        int $orderId,
        ?string $invoiceId,
        string $transactionId,
        bool $isSandbox,
        int $platform,
        CarbonImmutable $now,
        string $loggingProductSubName
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

        // null合体演算子の結果を変数化
        $resolvedCurrencyCode = $currencyCode ?? 'JPY';
        $platformProductId = $mstStoreProduct->getProductIdWebstore() ?? '';
        $purchasePriceStr = (string) $amount;

        // WebStore購入のTriggerを作成
        $trigger = new WebStorePurchasedTrigger(
            $oprProduct->getId(),
            $loggingProductSubName,
            $orderId,
            $invoiceId,
            $transactionId,
            $platformProductId,
            $mstStoreProduct->getId(),
            $resolvedCurrencyCode,
            $purchasePriceStr,
        );

        // 購入履歴保存を含む購入処理を実行（共通処理）
        $this->billingDelegator->purchasedForWebStore(
            $usrUserId,
            $oprProduct->getId(),
            $oprProduct->getPaidAmount(),
            $resolvedCurrencyCode,
            $receiptUniqueId,
            $orderId,
            $invoiceId,
            $transactionId,
            $mstStoreProduct->getId(),
            $platformProductId,
            $purchasePriceStr,
            0, // VIPポイントは0
            $isSandbox,
            $loggingProductSubName,
            $trigger
        );

        if ($oprProduct->isDiamond()) {
            $this->shopService->purchaseDiamond($usrUserId, $oprProduct, $now);
        } elseif ($oprProduct->isPack()) {
            // パック商品の場合は、パック内容を報酬として付与
            $mstPack = $this->mstPackRepository->getByProductSubId($oprProduct->getId(), true);
            $this->shopService->purchasePack($usrUserId, $oprProduct, $mstPack, $platform, $now);
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

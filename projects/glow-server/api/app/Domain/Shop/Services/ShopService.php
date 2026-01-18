<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Services\AdPlayService;
use App\Domain\Common\Services\ClockService;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Delegator\MessageDelegator;
use App\Domain\Resource\Entities\CurrencyTriggers\TradeShopItemTrigger;
use App\Domain\Resource\Entities\CurrencyTriggers\TradeShopPackTrigger;
use App\Domain\Resource\Entities\LogTriggers\TradeShopItemLogTrigger;
use App\Domain\Resource\Entities\Rewards\ShopItemReward;
use App\Domain\Resource\Entities\Rewards\ShopPackContentReward;
use App\Domain\Resource\Entities\Rewards\ShopPassReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstPackEntity;
use App\Domain\Resource\Mst\Entities\MstShopItemEntity;
use App\Domain\Resource\Mst\Entities\MstShopPassEntity;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Resource\Mst\Repositories\MstPackContentRepository;
use App\Domain\Resource\Mst\Repositories\MstPackRepository;
use App\Domain\Resource\Mst\Repositories\MstShopItemRepository;
use App\Domain\Resource\Mst\Repositories\MstShopPassEffectRepository;
use App\Domain\Resource\Mst\Repositories\MstShopPassI18nRepository;
use App\Domain\Resource\Mst\Repositories\MstShopPassRewardRepository;
use App\Domain\Resource\Mst\Repositories\OprProductRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Constants\ShopConstant;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Enums\PassEffectType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Shop\Models\UsrShopItemInterface;
use App\Domain\Shop\Models\UsrShopPassInterface;
use App\Domain\Shop\Repositories\LogTradeShopItemRepository;
use App\Domain\Shop\Repositories\UsrConditionPackRepository;
use App\Domain\Shop\Repositories\UsrShopItemRepository;
use App\Domain\Shop\Repositories\UsrShopPassRepository;
use App\Domain\Shop\Repositories\UsrTradePackRepository;
use App\Domain\Stage\Delegators\StageDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class ShopService
{
    public function __construct(
        // Mst Repository
        private MstPackContentRepository $mstPackContentRepository,
        private OprProductRepository $oprProductRepository,
        private MstShopPassI18nRepository $mstShopPassI18nRepository,
        private MstShopPassRewardRepository $mstShopPassRewardRepository,
        private MstPackRepository $mstPackRepository,
        private MstShopItemRepository $mstShopItemRepository,
        private MstShopPassEffectRepository $mstShopPassEffectRepository,
        private UsrTradePackRepository $usrTradePackRepository,
        // Usr Repository
        private UsrShopItemRepository $usrShopItemRepository,
        private UsrConditionPackRepository $usrConditionPackRepository,
        private UsrShopPassRepository $usrShopPassRepository,
        // Log Repository
        private LogTradeShopItemRepository $logTradeShopItemRepository,
        // Service
        private UsrStoreProductService $usrStoreProductService,
        // Delegator
        private UserDelegator $userDelegator,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
        private StageDelegator $stageDelegator,
        private RewardDelegator $rewardDelegator,
        private MessageDelegator $messageDelegator,
        // 課金基盤
        private AppCurrencyDelegator $appCurrencyDelegator,
        // common
        private Clock $clock,
        private ClockService $clockService,
        private AdPlayService $adPlayService,
    ) {
    }

    /**
     * ショップタイプごとに条件を確認してUsrShopItemの値をリセットする
     *
     * @return bool リセットしたかどうか true:リセットした false:リセットしなかった
     */
    public function resetUsrShopItem(
        UsrShopItemInterface $usrShopItem,
        MstShopItemEntity $mstShopItem,
        CarbonImmutable $now
    ): bool {
        $shopType = $mstShopItem->getShopType();
        if ($shopType === ShopType::DAILY->value) {
            // デイリー
            if ($this->clock->isFirstToday($usrShopItem->getLastResetAt())) {
                $usrShopItem->reset($now);
                return true;
            }
        } elseif ($shopType === ShopType::WEEKLY->value) {
            // ウィークリー
            if ($this->clock->isFirstWeek($usrShopItem->getLastResetAt())) {
                $usrShopItem->reset($now);
                return true;
            }
        } elseif ($shopType === ShopType::COIN->value && $mstShopItem->isCostAd()) {
            // コインのコストが広告のアイテムはデイリーリセット対象
            if ($this->clock->isFirstToday($usrShopItem->getLastResetAt())) {
                $usrShopItem->reset($now);
                return true;
            }
        }

        return false;
    }

    /**
     * リセット処理を施した、現在有効なUsrItemShopデータをDB更新なしで取得する
     *
     * @return Collection<UsrShopItemInterface>
     */
    public function fetchResetActiveUsrShopItemsWithoutSyncModels(
        string $usrUserId,
        CarbonImmutable $now
    ): Collection {
        $mstShopItems = $this->mstShopItemRepository->getActiveShopItems($now)
            ->keyBy(fn(MstShopItemEntity $mstShopItem) => $mstShopItem->getId());
        $usrShopItems = $this->usrShopItemRepository->getByMstShopItemIds(
            $usrUserId,
            $mstShopItems->map(fn(MstShopItemEntity $mstShopItem) => $mstShopItem->getId())
        );

        foreach ($usrShopItems as $usrShopItem) {
            /** @var \App\Domain\Shop\Models\UsrShopItemInterface $usrShopItem */
            $mstShopItem = $mstShopItems->get($usrShopItem->getMstShopItemId());
            if (is_null($mstShopItem)) {
                // マスターデータが存在しない
                continue;
            }

            $this->resetUsrShopItem($usrShopItem, $mstShopItem, $now);
        }

        return $usrShopItems;
    }

    /**
     * 商品を交換する
     * @param CurrentUser          $user
     * @param MstShopItemEntity    $mstShopItem
     * @param UsrShopItemInterface $usrShopItem
     * @param CarbonImmutable      $now
     * @return void
     * @throws GameException
     */
    public function tradeShopItem(
        CurrentUser $user,
        MstShopItemEntity $mstShopItem,
        UsrShopItemInterface $usrShopItem,
        CarbonImmutable $now
    ): void {
        // リセットの必要があれば実行
        $this->resetUsrShopItem($usrShopItem, $mstShopItem, $now);

        // 交換回数の検証
        $this->validateTradeCount($mstShopItem->getTradableCount(), $usrShopItem->getTradeCount());

        $resourceType = $mstShopItem->getResourceType();
        $resourceAmount = $mstShopItem->getResourceAmount();
        if ($resourceType === ShopConstant::IDLE_INCENTIVE_COIN_RESOURCE_TYPE) {
            // 放置収益連動コインの場合はタイプをコインに置き換える
            $resourceType = $this->convertToRealResourceType($resourceType);
            // resourceAmountに放置収益の放置時間(h)が入っているので分換算して獲得コインを計算
            $idleMinutes = $resourceAmount * 60;
            $idleMinutesCoinAmountMap = $this->idleIncentiveDelegator->calcCoinRewardAmounts(
                $user->id,
                collect([$idleMinutes]),
                $now,
            );
            $resourceAmount = $idleMinutesCoinAmountMap->get($idleMinutes) ?? 0;
        }

        // 交換回数を増やす
        $usrShopItem->incrementTradeCount();
        $this->usrShopItemRepository->syncModel($usrShopItem);

        // 交換物の付与
        $reward = new ShopItemReward(
            $resourceType,
            $mstShopItem->getResourceId(),
            $resourceAmount,
            $mstShopItem->getId(),
        );
        $this->rewardDelegator->addReward($reward);

        // 交換ログの保存
        $this->logTradeShopItemRepository->create(
            $user->id,
            $mstShopItem->getId(),
            $usrShopItem->getTradeCount(),
            $mstShopItem->getCostType(),
            $mstShopItem->getCostAmount(),
            $this->rewardDelegator->getSentRewards(ShopItemReward::class),
        );
    }

    /**
     * 必要なコストを消費する
     * @param string            $usrUserId
     * @param int               $tradeCount 交換するのはこれが何回目か
     * @param MstShopItemEntity $mstShopItem
     * @param int               $platform
     * @param string            $billingPlatform
     * @param CarbonImmutable   $now
     * @return void
     */
    public function consumeCost(
        string $usrUserId,
        int $tradeCount,
        MstShopItemEntity $mstShopItem,
        int $platform,
        string $billingPlatform,
        CarbonImmutable $now
    ): void {
        if ($tradeCount === 1 && $mstShopItem->isFirstTimeFree()) {
            // 初回無料の場合はコスト消費なし
            return;
        }

        $costType = $mstShopItem->getCostType();
        if ($costType === ShopItemCostType::FREE->value) {
            // 無料の場合はコスト消費しない
            return;
        }
        if ($costType === ShopItemCostType::AD->value) {
            // 広告の場合はコスト消費しない

            // ミッショントリガー送信
            $this->adPlayService->adPlay(
                $usrUserId,
                ContentType::TRADE_SHOP_ITEM->value,
                $mstShopItem->getId(),
                $now
            );

            return;
        }

        if ($costType === ShopItemCostType::COIN->value) {
            // コイン
            $this->userDelegator->consumeCoin(
                $usrUserId,
                $mstShopItem->getCostAmount(),
                $now,
                new TradeShopItemLogTrigger($mstShopItem->getId()),
            );
        } elseif ($costType === ShopItemCostType::PAID_DIAMOND->value) {
            // 有償一次通貨
            $this->appCurrencyDelegator->consumePaidDiamond(
                $usrUserId,
                $mstShopItem->getCostAmount(),
                $platform,
                $billingPlatform,
                new TradeShopItemTrigger($mstShopItem->getId(), $mstShopItem->getCostAmount()),
            );
        } elseif ($costType === ShopItemCostType::DIAMOND->value) {
            // 無償一次通貨→有償一次通貨の順で消費
            $this->appCurrencyDelegator->consumeDiamond(
                $usrUserId,
                $mstShopItem->getCostAmount(),
                $platform,
                $billingPlatform,
                new TradeShopItemTrigger($mstShopItem->getId(), $mstShopItem->getCostAmount()),
            );
        }
    }

    /**
     * 実配布するリソースタイプに変換する
     * @param string $resourceType
     * @return string
     */
    private function convertToRealResourceType(string $resourceType): string
    {
        if ($resourceType === ShopConstant::IDLE_INCENTIVE_COIN_RESOURCE_TYPE) {
            // 放置収益連動コインの場合は仮想的なリソースタイプなので実際に配布するコインに置き換える
            $resourceType = RewardType::COIN->value;
        }
        return $resourceType;
    }

    /**
     * ユーザーレベル開放パックを開放する
     * @param string $usrUserId
     * @param int    $userLevel
     * @param CarbonImmutable $now
     * @return Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> 開放した条件パック
     */
    public function releaseUserLevelPack(string $usrUserId, int $userLevel, CarbonImmutable $now): Collection
    {
        return $this->releaseConditionPackBySaleCondition(
            $usrUserId,
            SaleCondition::USER_LEVEL->value,
            $now,
            // ユーザーレベル判定
            function (string $conditionValue) use ($userLevel) {
                return (int) $conditionValue <= $userLevel;
            }
        );
    }

    /**
     * ステージクリア開放パックの開放
     * @param string $usrUserId
     * @param string $mstStageId
     * @param CarbonImmutable $now
     * @return Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> 開放した条件パック
     */
    public function releaseStageClearPack(string $usrUserId, string $mstStageId, CarbonImmutable $now): Collection
    {
        return $this->releaseConditionPackBySaleCondition(
            $usrUserId,
            SaleCondition::STAGE_CLEAR->value,
            $now,
            // クリアステージ判定
            function (string $conditionValue) use ($mstStageId) {
                return $conditionValue === $mstStageId;
            }
        );
    }

    /**
     * 開放条件付きパックの開放
     * @param string   $usrUserId
     * @param string   $saleCondition
     * @param CarbonImmutable   $now
     * @param callable $compareConditionValue 達成判定比較のクロージャ
     * @return Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> 開放した条件パック
     */
    private function releaseConditionPackBySaleCondition(
        string $usrUserId,
        string $saleCondition,
        CarbonImmutable $now,
        callable $compareConditionValue
    ): Collection {
        $usrConditionPacks = $this->usrConditionPackRepository->getList($usrUserId);
        $mstPackIds = $usrConditionPacks->map(fn($usrConditionPack) => $usrConditionPack->getMstPackId());
        // 未開放の条件開放パックを取得
        $mstPacks = $this->mstPackRepository->getBySaleCondition($saleCondition);
        // 解放済みではないパックを抽出
        $notReleasedMstPacks = $mstPacks->filter(function (MstPackEntity $entity) use ($mstPackIds) {
            return !$mstPackIds->contains($entity->getId());
        });

        if ($notReleasedMstPacks->isEmpty()) {
            return collect();
        }

        $productSubIds = $notReleasedMstPacks->map(fn(MstPackEntity $entity) => $entity->getProductSubId());

        $oprProducts = $this->oprProductRepository->getActiveProductsByIds($productSubIds, $now);

        $releaseConditionPack = $mstPacks->map(
            function (MstPackEntity $mstPack) use ($usrUserId, $oprProducts, $compareConditionValue, $now) {
                /** @var ?OprProductEntity $oprProduct */
                $oprProduct = $oprProducts->first(function (OprProductEntity $oprProduct) use ($mstPack) {
                    return $oprProduct->getId() === $mstPack->getProductSubId();
                });
                if ($oprProduct && $compareConditionValue($mstPack->getSaleConditionValue())) {
                    return [
                        'usr_user_id' => $usrUserId,
                        'mst_pack_id' => $mstPack->getId(),
                        'start_date' => $now->toDateTimeString(),
                    ];
                }
                return null;
            }
        )->filter();

        /** @var Collection $releaseConditionPack */
        if ($releaseConditionPack->isEmpty()) {
            // 開放対象の条件パックなしのため何もしない
            return collect();
        }

        return $this->usrConditionPackRepository->syncModelsByArray($releaseConditionPack->toArray());
    }

    /**
     * 開放可能なすべての条件開放パックを開放する
     * @param string $usrUserId
     * @param int    $level
     * @param CarbonImmutable $now
     * @return void
     */
    public function releaseConditionPacks(string $usrUserId, int $level, CarbonImmutable $now): void
    {
        // 開放済みの条件開放パック
        $usrConditionPacks = $this->usrConditionPackRepository->getList($usrUserId);
        $mstPackIds = $usrConditionPacks->map(fn($usrConditionPack) => $usrConditionPack->getMstPackId());

        // クリア済みのステージID
        $clearedMstStageIds = $this->stageDelegator->getClearedMstStageIds($usrUserId);

        // 未開放の条件開放パックを取得
        $mstPacks = $this->mstPackRepository->getSaleConditionPacks();
        // 解放済みではないパックを抽出
        $notReleasedMstPacks = $mstPacks->filter(function (MstPackEntity $entity) use ($mstPackIds) {
            return !$mstPackIds->contains($entity->getId());
        });

        if ($notReleasedMstPacks->isEmpty()) {
            return;
        }

        $productSubIds = $notReleasedMstPacks->map(fn(MstPackEntity $entity) => $entity->getProductSubId());

        $oprProducts = $this->oprProductRepository->getActiveProductsByIds($productSubIds, $now);

        $values = [];
        foreach ($oprProducts as $oprProductEntity) {
            /** @var \App\Domain\Resource\Mst\Entities\OprProductEntity $oprProductEntity */
            /** @var MstPackEntity $mstPack */
            $mstPack = $mstPacks->first(function (MstPackEntity $mstPack) use ($oprProductEntity) {
                return $oprProductEntity->getId() === $mstPack->getProductSubId();
            });
            if ($mstPack->isUserLevel() && (int) $mstPack->getSaleConditionValue() <= $level) {
                // ユーザーレベル条件を満たしている
                $values[] = [
                    'usr_user_id' => $usrUserId,
                    'mst_pack_id' => $mstPack->getId(),
                    'start_date' => $now->toDateTimeString(),
                ];
            } elseif ($mstPack->isStageClear() && $clearedMstStageIds->contains($mstPack->getSaleConditionValue())) {
                // ステージクリア条件を満たしている
                $values[] = [
                    'usr_user_id' => $usrUserId,
                    'mst_pack_id' => $mstPack->getId(),
                    'start_date' => $now->toDateTimeString(),
                ];
            }
        }

        if (count($values) === 0) {
            // 開放対象の条件パックなしのため何もしない
            return;
        }

        $this->usrConditionPackRepository->syncModelsByArray($values);
    }

    /**
     * 課金なしでパックを交換する処理
     */
    public function tradePack(
        string $usrUserId,
        MstPackEntity $mstPack,
        int $platform,
        string $billingPlatform,
        CarbonImmutable $now
    ): void {
        // usrTradePack側の交換数の確認
        $usrTradePack = $this->usrTradePackRepository->findOrCreate($usrUserId, $mstPack->getId(), $now);
        if ($mstPack->getPackType() === PackType::DAILY->value) {
            if ($this->clock->isFirstToday($usrTradePack->getLastResetAt())) {
                // 日跨ぎをしている場合は交換数をリセットする
                $usrTradePack->reset($now);
                $this->usrTradePackRepository->syncModel($usrTradePack);
            } else {
                // 日跨ぎをしていなければ交換数のチェックを行う
                $this->validateTradeCount(
                    $mstPack->getTradableCount(),
                    $usrTradePack->getDailyTradeCount(),
                );
            }
        }

        // パックのコスト消費処理
        $this->consumePackCost(
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now
        );

        // 購入回数を増やす
        $usrTradePack = $this->usrTradePackRepository->findOrCreate($usrUserId, $mstPack->getId(), $now);
        $usrTradePack->incrementTradeCount();
        $this->usrTradePackRepository->syncModel($usrTradePack);

        // 交換物の付与
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

    /**
     * パックのコスト消費処理
     * @param string            $usrUserId
     * @param MstPackEntity $mstShopPack
     * @param int               $platform
     * @param string            $billingPlatform
     * @param CarbonImmutable   $now
     * @return void
     */
    public function consumePackCost(
        string $usrUserId,
        MstPackEntity $mstShopPack,
        int $platform,
        string $billingPlatform,
        CarbonImmutable $now
    ): void {
        // usrTradePackを取得し実際の交換回数で判定する
        $usrTradePack = $this->usrTradePackRepository->findOrCreate($usrUserId, $mstShopPack->getId(), $now);

        // リセット処理後の実際の交換回数で初回無料判定
        if ($usrTradePack->getDailyTradeCount() === 0 && $mstShopPack->isFirstTimeFree()) {
            // 初回無料の場合はコスト消費なし
            return;
        }

        if ($mstShopPack->isFree()) {
            // 無料の場合はコスト消費しない
            return;
        } elseif ($mstShopPack->isAd()) {
            // 広告の場合はコスト消費しない

            // ミッショントリガー送信
            $this->adPlayService->adPlay(
                $usrUserId,
                ContentType::SHOP_PACK->value,
                $mstShopPack->getId(),
                $now
            );

            return;
        } elseif ($mstShopPack->isPaidDiamond()) {
            // 有償一次通貨
            $this->appCurrencyDelegator->consumePaidDiamond(
                $usrUserId,
                $mstShopPack->getCostAmount(),
                $platform,
                $billingPlatform,
                new TradeShopPackTrigger($mstShopPack->getId(), $mstShopPack->getCostAmount()),
            );
        } elseif ($mstShopPack->isDiamond()) {
            // 無償一次通貨→有償一次通貨の順で消費
            $this->appCurrencyDelegator->consumeDiamond(
                $usrUserId,
                $mstShopPack->getCostAmount(),
                $platform,
                $billingPlatform,
                new TradeShopPackTrigger($mstShopPack->getId(), $mstShopPack->getCostAmount()),
            );
        } else {
            // 対応コストタイプがなければエラーにする
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "Invalid cost type: {$mstShopPack->getCostType()} for pack id: {$mstShopPack->getId()}"
            );
        }
    }

    /**
     * 課金してパックを購入するときの処理
     */
    public function purchasePack(
        string $usrUserId,
        OprProductEntity $oprProduct,
        MstPackEntity $mstPack,
        int $platform,
        CarbonImmutable $now
    ): void {
        // 購入回数を増やす
        $this->usrStoreProductService->purchase($usrUserId, $oprProduct->getId(), $now);

        // 交換物の付与
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

    public function purchaseDiamond(
        string $usrUserId,
        OprProductEntity $oprProduct,
        CarbonImmutable $now
    ): void {
        // 購入回数を増やすのみで、billingAndExecCallbackの方で有償ダイヤが付与される
        $this->usrStoreProductService->purchase($usrUserId, $oprProduct->getId(), $now);
    }

    /**
     * 交換回数の検証
     * @param int|null $tradableCount
     * @param int      $tradeCount
     * @return void
     * @throws GameException
     */
    public function validateTradeCount(?int $tradableCount, int $tradeCount): void
    {
        if (is_null($tradableCount)) {
            // 交換回数無制限
            return;
        }

        if ($tradableCount <= $tradeCount) {
            // 交換上限に達している
            throw new GameException(
                ErrorCode::SHOP_TRADE_COUNT_LIMIT,
                "The trade limit has been reached. (tradable: $tradableCount trade: $tradeCount)"
            );
        }
    }

    /**
     * 条件パックが有効(購入または交換可能)か検証
     * @param MstPackEntity $mstPack
     * @param string        $usrUserId
     * @param CarbonImmutable        $now
     * @return void
     * @throws GameException
     */
    public function validateConditionPack(MstPackEntity $mstPack, string $usrUserId, CarbonImmutable $now): void
    {
        if (is_null($mstPack->getSaleCondition())) {
            // 条件パックではない
            return;
        }

        $usrConditionPack = $this->usrConditionPackRepository->get($usrUserId, $mstPack->getId());
        if (is_null($usrConditionPack)) {
            // 未開放
            throw new GameException(
                ErrorCode::SHOP_CONDITION_PACK_NOT_RELEASED,
                "The condition pack has not been released. (mst_pack_id: {$mstPack->getId()})"
            );
        }

        // 購入期間内か検証する
        if ($mstPack->getSaleHours() === null) {
            // 購入期間無制限
            return;
        }

        $limitDate = CarbonImmutable::parse($usrConditionPack->getStartDate())->addHours($mstPack->getSaleHours());
        if ($now->gte($limitDate)) {
            // 購入期限切れ
            throw new GameException(
                ErrorCode::SHOP_CONDITION_PACK_EXPIRED,
                "The condition pack has expired. (mst_pack_id: {$mstPack->getId()})"
            );
        }
    }

    /**
     * パスが有効(購入または交換可能)か検証
     * @param string $mstShopPassId
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return void
     * @throws GameException
     */
    public function validatePass(string $mstShopPassId, string $usrUserId, CarbonImmutable $now): void
    {
        $usrShopPass = $this->usrShopPassRepository->getActivePass($usrUserId, $mstShopPassId, $now);

        // usrShopPassがnullの場合はバリデーション処理なし
        if (is_null($usrShopPass)) {
            return;
        }

        // パス効果の取得
        $mstShopPassEffect = $this->mstShopPassEffectRepository
            ->getMstShopPassIdAndEffectType($mstShopPassId, PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value)
            ->first();

        $targetEffectType = '';
        if (!is_null($mstShopPassEffect)) {
            // スタミナ回復上限の場合は、targetEffectTypeに設定
            $targetEffectType = $mstShopPassEffect->getEffectType();
        }


        if ($targetEffectType !== PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value) {
            // 購入期限が切れていない場合
            throw new GameException(
                ErrorCode::SHOP_PASS_NOT_EXPIRED,
                "The pass in use. (mst_shop_pass_id: {$mstShopPassId})"
            );
        }

        // 期限内再購入可能パスの日付バリデーション
        $this->validateStaminaAddRecoveryLimitRepurchaseDays($usrShopPass->getEndAt(), $now);
    }

    /**
     * @return Collection<ShopPassReward>
     * @throws GameException
     */
    public function tradeShopPass(
        string $usrUserId,
        OprProductEntity $oprProduct,
        MstShopPassEntity $mstShopPass,
        CarbonImmutable $now
    ): Collection {
        // 購入回数を増やす
        $this->usrStoreProductService->purchase($usrUserId, $oprProduct->getId(), $now);

        // パスを付与する
        $this->resetOrCreate(
            $usrUserId,
            $mstShopPass->getId(),
            $now,
            $mstShopPass->getPassDurationDays()
        );

        // 即時報酬の付与
        $mstShopPassRewards = $this->mstShopPassRewardRepository->getImmediatelyMstShopPassId($mstShopPass->getId());
        $mstShopPassI18n = $this->mstShopPassI18nRepository->getMstShopPassId($mstShopPass->getId());

        $shopPassName = ShopConstant::DEFAULT_SHOP_PASS_NAME;
        if (isset($mstShopPassI18n)) {
            $shopPassName = $mstShopPassI18n->getName();
        }
        $rewardGroupId = (string) Uuid::uuid4();
        $shopPassRewards = collect();
        foreach ($mstShopPassRewards as $mstShopPassReward) {
            /** @var \App\Domain\Resource\Mst\Entities\MstShopPassRewardEntity $mstShopPassReward */
            $shopPassReward = new ShopPassReward(
                $mstShopPassReward->getResourceType(),
                $mstShopPassReward->getResourceId(),
                $mstShopPassReward->getResourceAmount(),
                $mstShopPassReward->getMstShopPassId(),
                $mstShopPassReward->getPassRewardType(),
            );
            $shopPassRewards->add($shopPassReward);

            $this->messageDelegator->addNewSystemMessage(
                $usrUserId,
                $rewardGroupId,
                $now->addDays(ShopConstant::IMMEDIATELY_PASS_REWARD_RECEIVE_DEADLINE),
                $shopPassReward,
                $shopPassName . MessageConstant::SHOP_PASS_TITLE,
                $shopPassName . MessageConstant::SHOP_PASS_BODY,
            );
        }

        return $shopPassRewards;
    }

    /**
     * パスのレコードが存在しない場合は新規作成、存在する場合はリセットする
     * @param string $usrUserId
     * @param string $mstShopPassId
     * @param CarbonImmutable $now
     * @param int $passDays
     * @return UsrShopPassInterface
     * @throws GameException
     */
    public function resetOrCreate(
        string $usrUserId,
        string $mstShopPassId,
        CarbonImmutable $now,
        int $passDays
    ): UsrShopPassInterface {
        $usrShopPass = $this->usrShopPassRepository->get($usrUserId, $mstShopPassId);
        $passTerm = $this->clockService->calcDaysRange($now, $passDays);
        $isUpdateDailyPassReward = false;
        if ($usrShopPass === null) {
            $usrShopPass = $this->usrShopPassRepository->create(
                $usrUserId,
                $mstShopPassId,
                $now,
                $passTerm->startAt,
                $passTerm->endAt,
            );
            $isUpdateDailyPassReward = true;
        } else {
            // 期限切れの場合はリセット
            if ($usrShopPass->getEndAt() <= $now->format('Y-m-d H:i:s')) {
                $usrShopPass->reset($now, $passTerm->startAt, $passTerm->endAt);
                $isUpdateDailyPassReward = true;
            } else {
                // 期限切れでない場合は、残り日数を計算してリセット
                $additionalDaysEndAt = $this->checkEndAtTargetDaysLeft($usrShopPass->getEndAt(), $passDays, $now);
                $usrShopPass->resetByRemainingTime($passTerm->startAt, $additionalDaysEndAt);
            }

            $this->usrShopPassRepository->syncModel($usrShopPass);
        }

        // 購入時即1日目の報酬を付与する
        if ($isUpdateDailyPassReward) {
            $this->updateDailyPassReward($usrUserId, $now);
        }
        return $usrShopPass;
    }

    /**
     * EndAtが現在から3日以内かどうかをチェックする
     * @param string $endAt
     * @param int $passDays
     * @param CarbonImmutable $now
     * @throws GameException
     * @return CarbonImmutable 延長した場合のパス終了日時
     */
    public function checkEndAtTargetDaysLeft(string $endAt, int $passDays, CarbonImmutable $now): CarbonImmutable
    {
        $this->validateStaminaAddRecoveryLimitRepurchaseDays($endAt, $now);

        $timestamp = $now->timestamp;
        $timeDiff = strtotime($endAt) - $timestamp;
        $daysLeft = (int)ceil($timeDiff / ShopConstant::DAY_TIME_SECONDS);

        // パスの日数に余った日数を足す
        $passTerm = $this->clockService->calcDaysRange($now, $passDays + $daysLeft);
        return $passTerm->endAt;
    }

    /**
     * パスの再購入可能日数を検証する
     * @param string $endAt
     * @param CarbonImmutable $now
     * @throws GameException
     * @return void
     */
    private function validateStaminaAddRecoveryLimitRepurchaseDays(string $endAt, CarbonImmutable $now)
    {
        $targetMin = ShopConstant::DAY_TIME_SECONDS * ShopConstant::ON_TIME_PASS_REPURCHASE_DAYS;
        $timestamp = $now->timestamp;

        $timeDiff = strtotime($endAt) - $timestamp;

        if ($timeDiff > $targetMin) {
            // 購入できる３日前以内になってない
            throw new GameException(
                ErrorCode::SHOP_PASS_NOT_EXPIRED,
                "The pass i t)",
            );
        }
    }

    /**
     * パス購入による毎日報酬の付与
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return void
     */
    public function updateDailyPassReward(string $usrUserId, CarbonImmutable $now)
    {
        // 毎日報酬は最終日が終わるまでは受け取り可
        $dayStartAt = $this->clock->calcDayStartDatetime($now);
        $activeUsrShopPasses = $this->usrShopPassRepository->getActiveList($usrUserId, $dayStartAt);
        if ($activeUsrShopPasses->isEmpty()) {
            // 検証すべきパスがない場合は何もしない
            return;
        }

        // アクティブになっているパスのIDリストを取得
        $mstShopPassIds = $activeUsrShopPasses->map(fn($activeUerShopPass) => $activeUerShopPass->getMstShopPassId());

        // 取得したパスIDに対応するリワードを取得
        $mstShopPassRewardList = $this->mstShopPassRewardRepository->getDailyMstShopPassIds($mstShopPassIds);
        $mstShopPassI18nList = $this->mstShopPassI18nRepository->getMstShopPassIds($mstShopPassIds);

        // パスリワードを取得
        foreach ($activeUsrShopPasses as $usrShopPass) {
            if (!isset($mstShopPassRewardList[$usrShopPass->getMstShopPassId()])) {
                continue;
            }
            $mstShopPassRewards = $mstShopPassRewardList[$usrShopPass->getMstShopPassId()];

            //  rewardの設定はあるがすでに受け取り済みの場合は何もしない
            $dailyLatestReceivedAt = $usrShopPass->getDailyLatestReceivedAt(); // 最終受取日

            if ($this->clock->isFirstToday($dailyLatestReceivedAt) === false) {
                // すでに受け取り済みの場合は何もしない
                continue;
            }

            $shopPassName = ShopConstant::DEFAULT_SHOP_PASS_NAME;
            if (isset($mstShopPassI18nList[$usrShopPass->getMstShopPassId()])) {
                $mstShopPassI18n = $mstShopPassI18nList[$usrShopPass->getMstShopPassId()];
                $shopPassName = $mstShopPassI18n->getName();
            }
            $rewardGroupId = (string) Uuid::uuid4();
            foreach ($mstShopPassRewards as $mstShopPassReward) {
                /** @var \App\Domain\Resource\Mst\Entities\MstShopPassRewardEntity $mstShopPassReward */
                $this->messageDelegator->addNewSystemMessage(
                    $usrUserId,
                    $rewardGroupId,
                    $now->addDays(ShopConstant::DAILY_PASS_REWARD_RECEIVE_DEADLINE),
                    new ShopPassReward(
                        $mstShopPassReward->getResourceType(),
                        $mstShopPassReward->getResourceId(),
                        $mstShopPassReward->getResourceAmount(),
                        $mstShopPassReward->getMstShopPassId(),
                        $mstShopPassReward->getPassRewardType(),
                    ),
                    $shopPassName . MessageConstant::SHOP_PASS_DAILY_REWARD_TITLE,
                    $shopPassName . MessageConstant::SHOP_PASS_DAILY_REWARD_BODY,
                );
            }
            // 受け取り回数をと受け取り日時の更新
            $usrShopPass->rewardReceived($now);
            $this->usrShopPassRepository->syncModel($usrShopPass);
        }
    }

    public function getUsrTradePackList(string $usrUserId, CarbonImmutable $now): Collection
    {

        $responseUsrTradePack = collect();
        $usrTradePacks = $this->usrTradePackRepository->getList($usrUserId);

        if ($usrTradePacks->isEmpty()) {
            // ユーザートレードパックが存在しない場合は空のコレクションを返す
            return $responseUsrTradePack;
        }

        foreach ($usrTradePacks as $usrTradePack) {
            if ($this->clock->isFirstToday($usrTradePack->getLastResetAt())) {
                $usrTradePack->reset($now);
            }
        }
        return $usrTradePacks;
    }
}

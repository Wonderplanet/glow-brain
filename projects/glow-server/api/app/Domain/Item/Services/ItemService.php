<?php

declare(strict_types=1);

namespace App\Domain\Item\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Constants\ItemConstant;
use App\Domain\Item\Enums\ItemTradeResetType;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\UsrItemTradeInterface;
use App\Domain\Item\Repositories\UsrItemTradeRepository;
use App\Domain\Resource\Entities\LogTriggers\ItemFragmentBoxLogTrigger;
use App\Domain\Resource\Entities\LogTriggers\ItemTradeLogTrigger;
use App\Domain\Resource\Entities\Rewards\ItemReward;
use App\Domain\Resource\Entities\Rewards\ItemTradeReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\Resource\Mst\Entities\MstItemRarityTradeEntity;
use App\Domain\Resource\Mst\Repositories\MstFragmentBoxGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstFragmentBoxRepository;
use App\Domain\Resource\Mst\Repositories\MstItemRarityTradeRepository;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;

class ItemService
{
    public function __construct(
        // MstRepository
        private MstItemRepository $mstItemRepository,
        private MstFragmentBoxRepository $mstFragmentBoxRepository,
        private MstFragmentBoxGroupRepository $mstFragmentBoxGroupRepository,
        private MstItemRarityTradeRepository $mstItemRarityTradeRepository,
        // UsrRepository
        private UsrItemTradeRepository $usrItemTradeRepository,
        // Service
        private UsrItemService $usrItemService,
        // Delegator
        private RewardDelegator $rewardDelegator,
        // Other
        private Clock $clock,
    ) {
    }
    /**
     * 消費アイテムのタイプに応じて、変換を行い、アイテム同士の交換を行う
     *
     * @param string $userId
     * @param int $platform
     * @param MstItemEntity $mstItem
     * @param int $amount
     * @param CarbonImmutable $now
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function apply(
        string $userId,
        int $platform,
        MstItemEntity $mstItem,
        int $amount,
        CarbonImmutable $now
    ): ?UsrItemTradeInterface {
        $usrItemTrade = null;

        switch ($mstItem->getItemType()) {
            case ItemType::RANDOM_FRAGMENT_BOX->value:
                // ランダムかけらBOX → キャラかけら 交換
                $this->applyRandomFragmentBox(
                    $userId,
                    $platform,
                    $mstItem,
                    $amount,
                    $now
                );
                break;
            case ItemType::CHARACTER_FRAGMENT->value:
                // キャラかけら → 選択かけらBOX 交換
                $usrItemTrade = $this->applyCharacterFragment(
                    $userId,
                    $platform,
                    $now,
                    $mstItem,
                    $amount,
                );
                break;
            default:
                throw new GameException(
                    ErrorCode::INVALID_PARAMETER,
                    "invalid item type. (itemType: {$mstItem->getItemType()})"
                );
        }

        return $usrItemTrade;
    }

    /**
     * @param string $userId
     * @param int $platform
     * @param MstItemEntity $mstItem
     * @param MstItemEntity $selectMstItem
     * @param int $amount
     * @param CarbonImmutable $now
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function applyWithSelectItem(
        string $userId,
        int $platform,
        MstItemEntity $mstItem,
        MstItemEntity $selectMstItem,
        int $amount,
        CarbonImmutable $now
    ): void {
        match ($mstItem->getItemType()) {
            ItemType::SELECTION_FRAGMENT_BOX->value => $this->applySelectionFragmentBox(
                $userId,
                $platform,
                $mstItem,
                $selectMstItem,
                $amount,
                $now
            ),
            default => throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "invalid item type. (itemType: {$mstItem->getItemType()})"
            ),
        };
    }

    /**
     * @param string $userId
     * @param int $platform
     * @param MstItemEntity $mstItem
     * @param int $amount
     * @param CarbonImmutable $now
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function applyRandomFragmentBox(
        string $userId,
        int $platform,
        MstItemEntity $mstItem,
        int $amount,
        CarbonImmutable $now
    ): void {
        if ($amount > ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "exceeded RandomFragmentBox exchange max. (amount: $amount)"
            );
        }

        // コストの消費
        $this->usrItemService->consumeItem(
            $userId,
            $mstItem->getId(),
            $amount,
            new ItemFragmentBoxLogTrigger($mstItem),
        );

        // ランダムに抽選してRewardDelegatorで付与する処理
        // FragmentBoxEntityを取得
        $mstFragmentBox = $this->mstFragmentBoxRepository->getByMstItemId($mstItem->getId(), true);
        // FragmentBoxGroupからラインナップを取得
        $mstFragmentBoxGroups = $this->mstFragmentBoxGroupRepository
            ->getActiveFragmentByGroupId($mstFragmentBox->getMstFragmentBoxGroupId(), $now);
        if ($mstFragmentBoxGroups->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_fragment_box_groups record is not found. (mstFragmentBoxGroupId: %s)',
                    $mstFragmentBox->getMstFragmentBoxGroupId()
                ),
            );
        }

        $rewards = collect();
        for ($i = 0; $i < $amount; $i++) {
            $mstFragmentBoxGroup = $mstFragmentBoxGroups->random();

            $rewards->push(new ItemReward(
                RewardType::ITEM->value,
                (string) $mstFragmentBoxGroup->getMstItemId(),
                1,
                $mstItem->getId(),
            ));
        }

        // 報酬配布リストに追加
        $this->rewardDelegator->addRewards($rewards);
    }

    /**
     * @param string $userId
     * @param int $platform
     * @param MstItemEntity $mstItem
     * @param MstItemEntity $selectMstItem
     * @param int $amount
     * @param CarbonImmutable $now
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function applySelectionFragmentBox(
        string $userId,
        int $platform,
        MstItemEntity $mstItem,
        MstItemEntity $selectMstItem,
        int $amount,
        CarbonImmutable $now
    ): void {
        if ($amount > ItemConstant::FRAGMENT_BOX_MAX_EXCHANGE) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "exceeded RandomFragmentBox exchange max. (amount: $amount)"
            );
        }
        // FragmentBoxEntityを取得
        $mstFragmentBox = $this->mstFragmentBoxRepository->getByMstItemId($mstItem->getId(), true);
        // FragmentBoxGroupのラインナップと一致した選択アイテムを取得
        $mstFragmentBoxGroup = $this->mstFragmentBoxGroupRepository->getActiveByGroupIdAndMstItemId(
            $mstFragmentBox->getMstFragmentBoxGroupId(),
            $selectMstItem->getId(),
            $now
        );
        // ラインナップに交換対象のアイテムが含まれているか確認
        if (is_null($mstFragmentBoxGroup)) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "selected item is not in the lineup or has expired. (selectMstItemId: {$selectMstItem->getId()})"
            );
        }

        // 報酬配布リストに追加
        $this->rewardDelegator->addReward(
            new ItemReward(
                RewardType::ITEM->value,
                (string) $selectMstItem->getId(),
                $amount,
                $mstItem->getId(),
            )
        );
    }

    /**
     * キャラのかけら → 選べるかけらBOX の交換処理
     * @param MstItemEntity $consumeMstItem 消費するキャラのかけらのアイテムマスタ
     * @param int $acquireAmount 選べるかけらBOXを獲得したい個数
     */
    private function applyCharacterFragment(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        MstItemEntity $consumeMstItem,
        int $acquireAmount,
    ): ?UsrItemTradeInterface {
        $rarity = $consumeMstItem->getRarity();
        $acquireMstItem = $this->mstItemRepository->getByTypeAndRarity(
            ItemType::SELECTION_FRAGMENT_BOX->value,
            $rarity,
            $now,
            true,
        );

        $mstItemRarityTrade = $this->mstItemRarityTradeRepository->getByRarity($rarity, true);

        // 獲得個数の整数確認
        $costAmount = $acquireAmount * $mstItemRarityTrade->getCostAmount();

        // コストの消費
        $this->usrItemService->consumeItem(
            $usrUserId,
            $consumeMstItem->getId(),
            $costAmount,
            new ItemTradeLogTrigger(
                LogResourceTriggerSource::ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX->value,
                $acquireMstItem->getId(),
            ),
        );

        // 交換数上限がある場合は、過去の交換数を確認
        $usrItemTrade = null;
        if ($mstItemRarityTrade->hasLimitTradeAmount()) {
            $usrItemTrade = $this->usrItemTradeRepository->getOrCreateByMstItemId(
                $usrUserId,
                $acquireMstItem->getId(),
                $now,
            );

            $this->resetUsrItemTrade(
                $mstItemRarityTrade,
                $usrItemTrade,
                $now,
            );
            $tradedAmount = $usrItemTrade->getResetTradeAmount() + $acquireAmount;

            if ($tradedAmount > $mstItemRarityTrade->getMaxTradableAmount()) {
                throw new GameException(
                    ErrorCode::ITEM_TRADE_AMOUNT_LIMIT_EXCEEDED,
                    "exceeded trade amount limit. (tradedAmount: $tradedAmount)"
                );
            }

            $usrItemTrade->addTradeAmount($acquireAmount);

            $this->usrItemTradeRepository->syncModel($usrItemTrade);
        }

        // 交換先の選択かけらBOXを、報酬配布リストに追加
        $this->rewardDelegator->addReward(
            new ItemTradeReward(
                RewardType::ITEM->value,
                $acquireMstItem->getId(),
                $acquireAmount,
                LogResourceTriggerSource::ITEM_TRADE_CHARACTER_FRAGMENT_TO_SELECTION_FRAGMENT_BOX,
                $consumeMstItem->getId(),
            )
        );

        return $usrItemTrade;
    }

    /**
     * マスタのリセットタイプに応じて、ユーザーが交換した回数をリセットする
     */
    private function resetUsrItemTrade(
        MstItemRarityTradeEntity $mstItemRarityTrade,
        UsrItemTradeInterface $usrItemTrade,
        CarbonImmutable $now
    ): void {
        if ($mstItemRarityTrade->hasResetType() === false) {
            // リセットなしなので、何もしない
            return;
        }

        $beforeResetAt = $usrItemTrade->getTradeAmountResetAt();
        $needReset = match ($mstItemRarityTrade->getResetTypeEnum()) {
            ItemTradeResetType::DAILY => $this->clock->isFirstToday($beforeResetAt),
            ItemTradeResetType::WEEKLY => $this->clock->isFirstWeek($beforeResetAt),
            ItemTradeResetType::MONTHLY => $this->clock->isFirstMonth($beforeResetAt),
            default => false,
        };

        if ($needReset === false) {
            // リセット不要なタイミングなので何もしない
            return;
        }

        $usrItemTrade->reset($now);
    }
}

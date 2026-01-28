<?php

declare(strict_types=1);

namespace App\Domain\Item\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Item\Services\ItemService;
use App\Domain\Item\Services\UsrItemService;
use App\Domain\Resource\Entities\LogTriggers\ItemFragmentBoxLogTrigger;
use App\Domain\Resource\Entities\Rewards\ItemReward;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Http\Responses\ResultData\ItemExchangeSelectItemResultData;

class ItemExchangeSelectItemUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private ItemService $itemService,
        private UsrItemService $usrItemService,
        // Repository
        private MstItemRepository $mstItemRepository,
        private UsrItemRepository $usrItemRepository,
        // Delegator
        private RewardDelegator $rewardDelegator,
        // Other
        private Clock $clock,
    ) {
    }

    public function exec(
        CurrentUser $user,
        int $platform,
        string $mstItemId,
        string $selectMstItemId,
        int $amount
    ): ItemExchangeSelectItemResultData {
        $now = $this->clock->now();
        $mstItem = $this->mstItemRepository->getActiveItemById($mstItemId, $now, true);
        $selectMstItem = $this->mstItemRepository->getActiveItemById($selectMstItemId, $now, true);

        // アイテム種別ごとの効果反映
        $this->itemService->applyWithSelectItem($user->id, $platform, $mstItem, $selectMstItem, $amount, $now);

        // コストの消費
        $this->usrItemService->consumeItem(
            $user->id,
            $mstItem->getId(),
            $amount,
            new ItemFragmentBoxLogTrigger($mstItem),
        );

        $this->applyUserTransactionChanges(function () use ($user, $platform, $now) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($user->id, $platform, $now);
        });

        return new ItemExchangeSelectItemResultData(
            $this->usrItemRepository->getChangedModels(),
            $this->rewardDelegator->getSentRewards(ItemReward::class),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Item\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Item\Services\ItemService;
use App\Domain\Resource\Entities\Rewards\ItemReward;
use App\Domain\Resource\Entities\Rewards\ItemTradeReward;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ItemConsumeResultData;

class ItemConsumeUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MstRepository
        private MstItemRepository $mstItemRepository,
        // Service
        private ItemService $itemService,
        private UsrItemRepository $usrItemRepository,
        // Delegator
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
        // Other
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param int $platform
     * @param string $mstItemId
     * @param int $amount
     * @return ItemConsumeResultData
     * @throws \Throwable
     */
    public function exec(CurrentUser $user, int $platform, string $mstItemId, int $amount): ItemConsumeResultData
    {
        $now = $this->clock->now();
        $mstItem = $this->mstItemRepository->getActiveItemById($mstItemId, $now, true);

        // アイテム種別ごとの効果反映
        $usrItemTrade = $this->itemService->apply($user->id, $platform, $mstItem, $amount, $now);

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use ($user, $platform, $now) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards($user->id, $platform, $now);
        });

        // レスポンス用意
        return new ItemConsumeResultData(
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($user->id)),
            $this->usrItemRepository->getChangedModels(),
            $this->rewardDelegator->getSentRewards(ItemReward::class),
            $this->rewardDelegator->getSentRewards(ItemTradeReward::class),
            $usrItemTrade,
        );
    }
}

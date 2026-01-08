<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstShopItemRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Repositories\UsrShopItemRepository;
use App\Domain\Shop\Services\ShopService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ShopTradeShopItemResultData;

class ShopTradeShopItemUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly Clock $clock,
        // MstRepository
        private readonly MstShopItemRepository $mstShopItemRepository,
        // UsrRepository
        private readonly UsrShopItemRepository $usrShopItemRepository,
        // Services
        private readonly ShopService $shopService,
        private readonly UsrModelDiffGetService $usrModelDiffGetService,
        // Delegator
        private readonly UserDelegator $userDelegator,
        private readonly RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string      $mstShopItemId
     * @param int         $platform
     * @param string      $billingPlatform
     * @return ShopTradeShopItemResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        string $mstShopItemId,
        int $platform,
        string $billingPlatform
    ): ShopTradeShopItemResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $mstShopItem = $this->mstShopItemRepository->getActiveShopItemById($mstShopItemId, $now, true);
        $usrShopItem = $this->usrShopItemRepository->findOrCreate($usrUserId, $mstShopItemId, $now);

        // ショップアイテムを交換
        $this->shopService->tradeShopItem(
            $user,
            $mstShopItem,
            $usrShopItem,
            $now
        );

        $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $mstShopItem,
            $usrShopItem,
            $platform,
            $billingPlatform,
            $now,
        ) {
            // コスト消費
            $this->shopService->consumeCost(
                $usrUserId,
                $usrShopItem->getTradeCount(),
                $mstShopItem,
                $platform,
                $billingPlatform,
                $now,
            );

            // 報酬配布実行
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $now);
        });

        // 交換物反映後の最新のプレイヤーデータ取得
        return new ShopTradeShopItemResultData(
            collect([$this->usrShopItemRepository->get($usrUserId, $mstShopItemId)]),
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId)),
            $this->usrModelDiffGetService->getChangedUsrItems(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Entities\Rewards\ShopPackContentReward;
use App\Domain\Resource\Mst\Repositories\MstPackRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Services\AppShopService;
use App\Domain\Shop\Services\ShopService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ShopTradePackResultData;

class ShopTradePackUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        // Mst Repository
        private MstPackRepository $mstPackRepository,
        // Service
        private ShopService $shopService,
        private AppShopService $appShopService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        // Delegator
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param int $platform
     * @param string $billingPlatform
     * @param string $oprProductId
     * @return ShopTradePackResultData
     * @throws GameException
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        string $billingPlatform,
        string $oprProductId,
    ): ShopTradePackResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // 有効期間チェックしてマスタ取得
        $oprProduct = $this->appShopService->getValidOprProductById($oprProductId, $now);
        if ($oprProduct->getProductType() !== ProductType::PACK->value) {
            // 万が一、パックとしてマスタ登録されていない場合はエラー
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "opr_products record exists but is not registered as a pack. (id: {$oprProductId})"
            );
        }

        // バリデーション
        $mstPack = $this->mstPackRepository->getByProductSubId($oprProductId, true);
        $this->shopService->validateConditionPack($mstPack, $usrUserId, $now);

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use (
            $usrUserId,
            $mstPack,
            $platform,
            $billingPlatform,
            $now,
        ) {
            $this->shopService->tradePack(
                $usrUserId,
                $mstPack,
                $platform,
                $billingPlatform,
                $now,
            );
        });

        // APIレスポンス取得
        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

        return new ShopTradePackResultData(
            $this->makeUsrParameterData($usrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->shopService->getUsrTradePackList($usrUserId, $now),
            $this->rewardDelegator->getSentRewards(ShopPackContentReward::class),
        );
    }
}

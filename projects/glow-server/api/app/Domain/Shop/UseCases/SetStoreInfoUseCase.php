<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Shop\Services\AppShopService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ShopSetStoreInfoResultData;

class SetStoreInfoUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private AppShopService $appShopService,
        // Delegator
        private UserDelegator $userDelegator,
        // Common
        private Clock $clock
    ) {
    }

    /**
     * @param int $birthDate 8桁の生年月日の数値データ（例: 20011205 = 2001年12月5日が誕生日）
     */
    public function exec(
        CurrentUser $currentUser,
        int $birthDate
    ): ShopSetStoreInfoResultData {
        $usrUserId = $currentUser->getId();
        $now = $this->clock->now();

        $this->userDelegator->setBirthDate($usrUserId, $birthDate, $now);

        // トランザクション処理
        list(
            $usrStoreInfo,
        ) = $this->applyUserTransactionChanges(function () use ($usrUserId, $now, $birthDate) {
            $usrStoreInfo = $this->appShopService->initUsrStoreInfo(
                $usrUserId,
                $now,
                $birthDate,
            );

            return [
                $usrStoreInfo,
            ];
        });

        return new ShopSetStoreInfoResultData($usrStoreInfo);
    }
}

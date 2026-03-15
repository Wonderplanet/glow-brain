<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Shop\Services\ShopPurchaseHistoryService;
use App\Http\Responses\ResultData\ShopPurchaseHistoryResultData;

class ShopPurchaseHistoryUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private ShopPurchaseHistoryService $shopPurchaseHistoryService,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string $billingPlatform
     * @return ShopPurchaseHistoryResultData
     * @throws GameException
     * @throws \Throwable
     */
    public function exec(CurrentUser $user, string $billingPlatform): ShopPurchaseHistoryResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $currencyPurchases = $this->shopPurchaseHistoryService->getCurrencyPurchaseHistory(
            $usrUserId,
            $billingPlatform,
            $now
        );

        return new ShopPurchaseHistoryResultData(
            $currencyPurchases
        );
    }
}

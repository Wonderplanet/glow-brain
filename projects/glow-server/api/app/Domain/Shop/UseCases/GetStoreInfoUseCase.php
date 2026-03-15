<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Http\Responses\ResultData\GetStoreInfoResultData;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;

class GetStoreInfoUseCase
{
    use UseCaseTrait;

    public function __construct(
        private BillingDelegator $billingDelegator,
    ) {
    }

    /**
     * ショップ情報取得
     *
     * @param CurrentUser $currentUser
     * @return GetStoreInfoResultData
     */
    public function __invoke(
        CurrentUser $currentUser,
    ): GetStoreInfoResultData {
        $usrStoreInfo = $this->billingDelegator->getStoreInfo((string) $currentUser->getId());

        $this->processWithoutUserTransactionChanges();

        return new GetStoreInfoResultData($usrStoreInfo);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;

class ShopSetStoreInfoResultData
{
    public function __construct(
        public UsrStoreInfoEntity $usrStoreInfo,
    ) {
    }
}

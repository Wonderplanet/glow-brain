<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;

class ShopBuyCoinDiamondResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\User\Models\UsrUserBuyCountInterface;
use App\Http\Responses\Data\UsrParameterData;

class ShopBuyCoinFreeResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrUserBuyCountInterface $usrUserBuyCount,
    ) {
    }
}

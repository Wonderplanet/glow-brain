<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\User\Models\UsrUserBuyCountInterface;
use App\Http\Responses\Data\UsrParameterData;

class UserBuyStaminaAdResultData
{
    /**
     */
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrUserBuyCountInterface $usrUserBuyCount,
    ) {
    }
}

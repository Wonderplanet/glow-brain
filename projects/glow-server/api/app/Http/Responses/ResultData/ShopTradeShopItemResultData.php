<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class ShopTradeShopItemResultData
{
    /**
     * @param Collection                $usrShopItems
     * @param Collection                $usrItems
     */
    public function __construct(
        public Collection $usrShopItems,
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
    ) {
    }
}

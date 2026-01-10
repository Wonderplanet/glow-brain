<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class ShopTradePackResultData
{
    /**
     * @param UsrParameterData $usrUserParameter
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface> $usrUnits
     * @param Collection<\App\Domain\Shop\Models\UsrTradePackInterface> $usrTradePacks
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     * 
     */
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $usrTradePacks,
        public Collection $rewards,
    ) {
    }
}

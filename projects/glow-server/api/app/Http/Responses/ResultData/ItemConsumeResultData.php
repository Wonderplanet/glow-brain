<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Item\Models\UsrItemTradeInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class ItemConsumeResultData
{
    /**
     * @param UsrParameterData $usrUserParameter
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<\App\Domain\Resource\Entities\Rewards\ItemReward> $itemRewards
     * @param Collection<\App\Domain\Resource\Entities\Rewards\ItemTradeReward> $itemTradeRewards
     */
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public Collection $itemRewards,
        public Collection $itemTradeRewards,
        public ?UsrItemTradeInterface $usrItemTrade,
    ) {
    }
}

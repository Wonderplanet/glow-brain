<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class ItemExchangeSelectItemResultData
{
    /**
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<\App\Domain\Resource\Entities\Rewards\ItemReward> $itemRewards
     */
    public function __construct(
        public Collection $usrItems,
        public Collection $itemRewards,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class TradeShopItemLogTrigger extends LogTrigger
{
    private string $mstShopItemId;

    public function __construct(string $mstShopItemId)
    {
        $this->mstShopItemId = $mstShopItemId;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::TRADE_SHOP_ITEM_COST->value,
            $this->mstShopItemId,
        );
    }
}

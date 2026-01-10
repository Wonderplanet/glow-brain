<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class ShopPurchasedLogTrigger extends LogTrigger
{
    private string $oprProductId;

    public function __construct(string $oprProductId)
    {
        $this->oprProductId = $oprProductId;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::SHOP_PURCHASED_REWARD->value,
            $this->oprProductId,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class ExchangeTradeLogTrigger extends LogTrigger
{
    private string $mstExchangeId;
    private string $mstExchangeLineupId;

    public function __construct(string $mstExchangeId, string $mstExchangeLineupId)
    {
        $this->mstExchangeId = $mstExchangeId;
        $this->mstExchangeLineupId = $mstExchangeLineupId;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::EXCHANGE_TRADE_COST->value,
            $this->mstExchangeId . ':' . $this->mstExchangeLineupId,
        );
    }
}

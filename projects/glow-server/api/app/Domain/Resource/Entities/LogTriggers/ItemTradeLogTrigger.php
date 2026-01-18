<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;

class ItemTradeLogTrigger extends LogTrigger
{
    private string $triggerSource;
    private string $acquireMstItemId;

    public function __construct(
        string $triggerSource,
        string $acquireMstItemId,
    ) {
        $this->triggerSource = $triggerSource;
        $this->acquireMstItemId = $acquireMstItemId;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            $this->triggerSource,
            $this->acquireMstItemId,
        );
    }
}

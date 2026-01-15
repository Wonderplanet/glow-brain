<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class BoxGachaLogTrigger extends LogTrigger
{
    public function __construct(
        private readonly string $mstBoxGachaId,
        private readonly int $boxLevel,
    ) {
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::BOX_GACHA_COST->value,
            $this->mstBoxGachaId,
            (string)$this->boxLevel,
        );
    }
}

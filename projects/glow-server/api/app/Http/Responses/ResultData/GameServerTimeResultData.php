<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Carbon\CarbonImmutable;

class GameServerTimeResultData
{
    public function __construct(
        public CarbonImmutable $serverTime,
    ) {
    }
}

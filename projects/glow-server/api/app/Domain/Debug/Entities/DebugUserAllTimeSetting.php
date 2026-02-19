<?php

declare(strict_types=1);

namespace App\Domain\Debug\Entities;

use Carbon\CarbonImmutable;

class DebugUserAllTimeSetting
{
    private int $diffMilliSeconds;

    public function __construct(
        CarbonImmutable $targetDateTime,
        CarbonImmutable $now = new CarbonImmutable(),
    ) {
        $this->diffMilliSeconds = (int) $now->diffInMilliseconds($targetDateTime, false);
    }

    public function getUserAllTime(CarbonImmutable $now = new CarbonImmutable()): CarbonImmutable
    {
        return $now->addMilliseconds($this->diffMilliSeconds);
    }
}

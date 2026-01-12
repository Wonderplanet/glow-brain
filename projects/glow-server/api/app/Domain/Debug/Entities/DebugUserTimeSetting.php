<?php

declare(strict_types=1);

namespace App\Domain\Debug\Entities;

use Carbon\CarbonImmutable;

class DebugUserTimeSetting
{
    private int $diffMilliseconds;

    public function __construct(
        CarbonImmutable $targetDateTime,
        CarbonImmutable $now = new CarbonImmutable(),
    ) {
        $this->diffMilliseconds = (int) $now->diffInMilliseconds($targetDateTime, false);
    }

    public function getUserTime(CarbonImmutable $now = new CarbonImmutable()): CarbonImmutable
    {
        return $now->addMilliseconds($this->diffMilliseconds);
    }
}

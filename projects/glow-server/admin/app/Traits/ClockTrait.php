<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entities\Clock;
use Carbon\CarbonImmutable;

trait ClockTrait
{
    public function now(): CarbonImmutable
    {
        /** @var Clock $clock */
        $clock = app()->make(Clock::class);
        return $clock->now();
    }
}

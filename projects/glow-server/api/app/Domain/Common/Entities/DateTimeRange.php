<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

use Carbon\CarbonImmutable;

class DateTimeRange
{
    public function __construct(
        public readonly CarbonImmutable $startAt,
        public readonly CarbonImmutable $endAt,
    ) {
    }
}

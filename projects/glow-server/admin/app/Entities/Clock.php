<?php

declare(strict_types=1);

namespace App\Entities;

use App\Constants\SystemConstants;
use App\Domain\Common\Entities\Clock as BaseClock;
use Carbon\CarbonImmutable;

class Clock extends BaseClock
{
    public function applyTimezoneForQuery(CarbonImmutable $targetAt): CarbonImmutable
    {
        return $targetAt->setTimezone(SystemConstants::DB_TIMEZONE);
    }

    public function parseAndApplyTimezoneForQuery(string $targetAt): CarbonImmutable
    {
        $carbon = CarbonImmutable::parse($targetAt);
        return $this->applyTimezoneForQuery($carbon);
    }
}

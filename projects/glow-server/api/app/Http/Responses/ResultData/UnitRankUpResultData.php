<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Unit\Models\UsrUnitInterface as UsrUnit;
use Illuminate\Support\Collection;

class UnitRankUpResultData
{
    /**
     * @param UsrUnit $usrUnit
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     */
    public function __construct(
        public UsrUnit $usrUnit,
        public Collection $usrItems,
    ) {
    }
}

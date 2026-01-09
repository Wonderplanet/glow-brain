<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

class UnitGradeUpResultData
{
    /**
     * @param UsrUnitInterface $usrUnit
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     */
    public function __construct(
        public UsrUnitInterface $usrUnit,
        public Collection $usrItems,
    ) {
    }
}

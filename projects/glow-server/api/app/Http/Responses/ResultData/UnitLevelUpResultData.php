<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Unit\Models\UsrUnitInterface as UsrUnit;
use App\Http\Responses\Data\UsrParameterData;

class UnitLevelUpResultData
{
    /**
     * @param UsrUnit $usrUnit
     */
    public function __construct(
        public UsrUnit $usrUnit,
        public UsrParameterData $usrUserParameter,
    ) {
    }
}

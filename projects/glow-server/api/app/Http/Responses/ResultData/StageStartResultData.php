<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\Data\UsrStageStatusData;

class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrStageStatusData $usrStageStatus
    ) {
    }
}

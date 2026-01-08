<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrStageStatusData;

class StageContinueAdResultData
{
    public function __construct(
        public UsrStageStatusData $usrStageStatusData,
    ) {
    }
}

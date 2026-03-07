<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;

class OutpostEnhanceResultData
{
    public function __construct(
        public int $beforeLevel,
        public int $afterLevel,
        public UsrParameterData $usrUserParameter,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class GachaHistoryResultData
{
    public function __construct(
        public Collection $gachaHistories,
    ) {
    }
}

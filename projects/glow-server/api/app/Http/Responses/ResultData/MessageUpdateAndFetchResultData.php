<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class MessageUpdateAndFetchResultData
{
    public function __construct(
        public Collection $messageDataList,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class PartySaveResultData
{
    public function __construct(
        public Collection $usrParties,
    ) {
    }
}

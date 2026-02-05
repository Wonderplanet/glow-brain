<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\LinkBnidData;

class UserLinkBnidResultData
{
    public function __construct(
        public LinkBnidData $linkBnidData,
    ) {
    }
}

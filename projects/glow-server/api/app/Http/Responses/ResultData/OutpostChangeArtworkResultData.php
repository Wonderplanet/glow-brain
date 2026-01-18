<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Outpost\Models\UsrOutpostInterface;

class OutpostChangeArtworkResultData
{
    public function __construct(
        public UsrOutpostInterface $usrOutpost,
    ) {
    }
}

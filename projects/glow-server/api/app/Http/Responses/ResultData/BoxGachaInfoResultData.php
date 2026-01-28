<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\BoxGacha\Models\UsrBoxGachaInterface;

class BoxGachaInfoResultData
{
    public function __construct(
        public UsrBoxGachaInterface $usrBoxGacha,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\BnidLinkedUserData;

class UserLinkBnidConfirmResultData
{
    public function __construct(
        public BnidLinkedUserData $bnidLinkedUserData,
    ) {
    }
}

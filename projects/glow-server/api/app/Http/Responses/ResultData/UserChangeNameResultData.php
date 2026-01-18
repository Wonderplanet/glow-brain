<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\User\Models\UsrUserProfileInterface;
use App\Http\Responses\Data\UsrParameterData;

class UserChangeNameResultData
{
    /**
     * @param UsrUserProfileInterface $usrUserProfile,
     */
    public function __construct(
        public UsrUserProfileInterface $usrUserProfile,
        public UsrParameterData $usrUserParameter,
    ) {
    }
}

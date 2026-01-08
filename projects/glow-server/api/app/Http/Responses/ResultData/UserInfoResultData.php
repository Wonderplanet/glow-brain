<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\User\Models\UsrUserProfileInterface;

class UserInfoResultData
{
    /**
     * @param UsrUserProfileInterface $usrUserProfile,
     */
    public function __construct(
        public UsrUserProfileInterface $usrUserProfile,
    ) {
    }
}

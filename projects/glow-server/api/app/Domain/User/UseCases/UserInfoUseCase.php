<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use App\Http\Responses\ResultData\UserInfoResultData;

class UserInfoUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrUserProfileRepository $usrUserProfileRepository,
    ) {
    }

    public function exec(CurrentUser $user): UserInfoResultData
    {
        $usrUserId = $user->id;

        $usrUserProfile = $this->usrUserProfileRepository->findByUsrUserId($usrUserId);

        $this->processWithoutUserTransactionChanges();

        return new UserInfoResultData($usrUserProfile);
    }
}

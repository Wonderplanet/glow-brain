<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Domain\User\Services\UserAccountLinkService;
use App\Http\Responses\ResultData\UserLinkBnidConfirmResultData;

class UserLinkBnidConfirmUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly UserAccountLinkService $userAccountLinkService,
        private readonly UsrUserRepository $usrUserRepository,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string      $code
     * @param string $ip
     * @return UserLinkBnidConfirmResultData
     * @throws GameException
     */
    public function exec(
        CurrentUser $user,
        string $code,
        string $ip,
    ): UserLinkBnidConfirmResultData {
        $usrUser = $this->usrUserRepository->findById($user->id);
        $this->userAccountLinkService->validateAccountLinkingRestriction($usrUser, myAccount: true);

        $bnidLinkedUserData = $this->userAccountLinkService->linkBnidConfirm($code, $ip);

        $this->processWithoutUserTransactionChanges();
        return new UserLinkBnidConfirmResultData($bnidLinkedUserData);
    }
}

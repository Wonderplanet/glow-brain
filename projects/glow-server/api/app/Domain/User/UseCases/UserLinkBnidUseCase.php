<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Domain\User\Services\UserAccountLinkService;
use App\Http\Responses\ResultData\UserLinkBnidResultData;

class UserLinkBnidUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly Clock $clock,
        private readonly UserAccountLinkService $userAccountLinkService,
        private readonly UsrUserRepository $usrUserRepository,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $platform,
        string $code,
        bool $isHome,
        string $accessToken,
        string $ip,
    ): UserLinkBnidResultData {
        return $this->applyUserTransactionChanges(function () use (
            $user,
            $platform,
            $code,
            $isHome,
            $accessToken,
            $ip
        ) {
            $now = $this->clock->now();
            $usrUser = $this->usrUserRepository->findById($user->id);
            $this->userAccountLinkService->validateAccountLinkingRestriction($usrUser, myAccount: true);
            $linkBnidData = $this->userAccountLinkService->linkBnid(
                $user->id,
                $platform,
                $code,
                $isHome,
                $accessToken,
                $ip,
                $now
            );

            return new UserLinkBnidResultData($linkBnidData);
        });
    }
}

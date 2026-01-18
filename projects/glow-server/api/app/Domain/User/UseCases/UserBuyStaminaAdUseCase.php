<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Repositories\UsrUserBuyCountRepository;
use App\Domain\User\Repositories\UsrUserRepository as UsrUserRepository;
use App\Domain\User\Services\UserBuyStaminaService;
use App\Domain\User\Services\UserService;
use App\Http\Responses\ResultData\UserBuyStaminaAdResultData;

class UserBuyStaminaAdUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UserService $userService,
        private UsrUserRepository $usrUserRepository,
        private UserBuyStaminaService $userBuyStaminaService,
        private UsrUserBuyCountRepository $usrUserBuyCountRepository,
    ) {
    }

    public function exec(CurrentUser $user): UserBuyStaminaAdResultData
    {
        $usrUser = $this->usrUserRepository->findById($user->id);
        $now = $this->clock->now();

        $this->userBuyStaminaService->buyStaminaAd($usrUser->getId(), $now);
        $usrUserParameter = $this->userService->recoveryStamina($usrUser->getId(), $now);
        $usrUserBuyCount = $this->usrUserBuyCountRepository->findByUsrUserId($usrUser->getId());

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new UserBuyStaminaAdResultData(
            $this->makeUsrParameterData($usrUserParameter),
            $usrUserBuyCount,
        );
    }
}

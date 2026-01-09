<?php

declare(strict_types=1);

namespace App\Domain\User\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Repositories\UsrUserBuyCountRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Services\UserBuyStaminaService;
use App\Http\Responses\ResultData\UserBuyStaminaDiamondResultData;

class UserBuyStaminaDiamondUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UserBuyStaminaService $userBuyStaminaService,
        private UsrUserBuyCountRepository $usrUserBuyCountRepository,
        private UsrUserParameterRepository $usrUserParameterRepository,
    ) {
    }

    public function exec(CurrentUser $user, int $platform, string $billingPlatform): UserBuyStaminaDiamondResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // トランザクション処理
        list(
            $afterUsrUserParameter,
        ) = $this->applyUserTransactionChanges(function () use ($usrUserId, $platform, $billingPlatform, $now) {
            $this->userBuyStaminaService->buyStaminaDiamond($usrUserId, $platform, $billingPlatform, $now);
            $afterUsrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);

            return [
                $afterUsrUserParameter,
            ];
        });

        // レスポンス用意
        $usrUserBuyCount = $this->usrUserBuyCountRepository->findByUsrUserId($usrUserId);

        return new UserBuyStaminaDiamondResultData(
            $this->makeUsrParameterData($afterUsrUserParameter),
            $usrUserBuyCount,
        );
    }
}

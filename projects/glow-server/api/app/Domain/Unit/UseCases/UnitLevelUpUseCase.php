<?php

declare(strict_types=1);

namespace App\Domain\Unit\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Unit\Services\UnitLevelUpService;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\UnitLevelUpResultData;

class UnitLevelUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UserDelegator $userDelegator,
        private UnitLevelUpService $unitLevelUpService
    ) {
    }

    public function exec(CurrentUser $user, string $usrUnitId, int $level): UnitLevelUpResultData
    {
        $now = $this->clock->now();
        $usrUnit = $this->unitLevelUpService->levelUp($user->id, $usrUnitId, $level, $now);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス作成
        return new UnitLevelUpResultData(
            $usrUnit,
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($user->id)),
        );
    }
}

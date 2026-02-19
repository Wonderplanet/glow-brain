<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Services\AdventBattleCleanupService;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Http\Responses\ResultData\AdventBattleCleanupResultData;

class AdventBattleCleanupUseCase
{
    use UseCaseTrait;

    public function __construct(
        private AdventBattleCleanupService $adventBattleCleanupService,
    ) {
    }

    public function exec(CurrentUser $user): AdventBattleCleanupResultData
    {
        $usrUserId = $user->id;
        $this->adventBattleCleanupService->cleanup($usrUserId);

        $this->applyUserTransactionChanges();

        return new AdventBattleCleanupResultData();
    }
}

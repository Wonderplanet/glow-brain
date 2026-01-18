<?php

declare(strict_types=1);

namespace App\Domain\Unit\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Unit\Services\UnitRankUpService;
use App\Http\Responses\ResultData\UnitRankUpResultData;

class UnitRankUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UnitRankUpService $unitRankUpService,
        private UsrModelDiffGetService $usrModelDiffGetService,
    ) {
    }

    public function exec(CurrentUser $user, string $unit_id): UnitRankUpResultData
    {
        $usrUnit = $this->unitRankUpService->rankUp($unit_id, $user->id, $this->clock->now());

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス作成
        return new UnitRankUpResultData($usrUnit, $this->usrModelDiffGetService->getChangedUsrItems());
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Unit\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Unit\Services\UnitGradeUpService;
use App\Http\Responses\ResultData\UnitGradeUpResultData;

class UnitGradeUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UnitGradeUpService $unitGradeUpService,
        private UsrModelDiffGetService $usrModelDiffGetService,
    ) {
    }

    public function exec(CurrentUser $user, string $usrUnitId): UnitGradeUpResultData
    {
        $usrUnit = $this->unitGradeUpService->gradeUp($usrUnitId, $user->id);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス作成
        return new UnitGradeUpResultData($usrUnit, $this->usrModelDiffGetService->getChangedUsrItems());
    }
}

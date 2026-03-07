<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Encyclopedia\Services\ArtworkGradeUpService;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Http\Responses\ResultData\ArtworkGradeUpResultData;

class ArtworkGradeUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly ArtworkGradeUpService $artworkGradeUpService,
        private readonly UsrModelDiffGetService $usrModelDiffGetService,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $mstArtworkId,
    ): ArtworkGradeUpResultData {
        $usrUserId = $user->id;

        // グレードアップ実行
        $usrArtwork = $this->artworkGradeUpService->gradeUp($usrUserId, $mstArtworkId);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        // レスポンス用意
        return new ArtworkGradeUpResultData(
            $usrArtwork,
            $this->usrModelDiffGetService->getChangedUsrItems(),
        );
    }
}

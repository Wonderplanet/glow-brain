<?php

declare(strict_types=1);

namespace App\Domain\Stage\Delegators;

use App\Domain\Resource\Mst\Entities\MstQuestEntity;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Stage\Services\StageEndTutorialQuestService;
use App\Domain\Stage\Services\StageStartTutorialQuestService;
use App\Http\Responses\Data\UsrStageStatusData;
use Carbon\CarbonImmutable;

/**
 * Stageドメインの内でTutorialドメインで使用する処理をまとめたデリゲータークラス
 */
class StageTutorialDelegator
{
    public function __construct(
        private StageStartTutorialQuestService $stageStartTutorialQuestService,
        private StageEndTutorialQuestService $stageEndTutorialQuestService,
    ) {
    }

    public function startTutorialStageAndGetUsrStageStatusData(
        string $usrUserId,
        int $partyNo,
        MstStageEntity $mstStage,
        MstQuestEntity $mstQuest,
        CarbonImmutable $now,
    ): UsrStageStatusData {
        return $this->stageStartTutorialQuestService->startAndGetUsrStageStatusData(
            $usrUserId,
            $partyNo,
            $mstStage,
            $mstQuest,
            $now,
        );
    }

    public function endTutorialStage(
        string $usrUserId,
        MstStageEntity $mstStage,
    ): void {
        $this->stageEndTutorialQuestService->endTutorial($usrUserId, $mstStage);
    }
}

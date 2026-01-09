<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Repositories\MstStageClearTimeRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Entities\StageInGameBattleLog;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\UsrStageInterface;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageEndNormalQuestService extends StageEndQuestService
{
    public function __construct(
        // 具象
        protected UsrStageRepository $usrStageNormalRepository, // 親クラスのプロパティ名と重複しないためにNormalを追加
        // MstRepository
        protected MstStageRepository $mstStageRepository,
        protected MstStageClearTimeRewardRepository $mstStageClearTimeRewardRepository,
        // UsrRepository
        protected UsrStageSessionRepository $usrStageSessionRepository,
        // Service
        protected StageService $stageService,
        protected StageMissionTriggerService $stageMissionTriggerService,
        protected StageLogService $stageLogService,
        // Delegator
        protected UserDelegator $userDelegator,
        protected UnitDelegator $unitDelegator,
        protected RewardDelegator $rewardDelegator,
        protected EncyclopediaDelegator $encyclopediaDelegator,
        protected PartyDelegator $partyDelegator,
    ) {
        parent::__construct(
            $usrStageNormalRepository,
            $mstStageRepository,
            $mstStageClearTimeRewardRepository,
            $usrStageSessionRepository,
            $stageService,
            $stageMissionTriggerService,
            $stageLogService,
            $userDelegator,
            $unitDelegator,
            $rewardDelegator,
            $encyclopediaDelegator,
            $partyDelegator,
            QuestType::NORMAL,
        );
    }

    /**
     * @inheritDoc
     */
    public function end(
        string $usrUserId,
        MstStageEntity $mstStage,
        UsrStageSessionInterface $usrStageSession,
        StageInGameBattleLog $inGameBattleLogData,
        Collection $oprCampaigns,
        CarbonImmutable $now,
    ): void {
        $mstStageId = $mstStage->getId();
        $lapCount = $usrStageSession->getAutoLapCount();

        /** @var UsrStageInterface $usrStage */
        $usrStage = $this->usrStageRepository->findByMstStageId($usrUserId, $mstStageId);
        $partyNo = $usrStageSession->getPartyNo();

        $this->validateCanEnd($mstStage, $usrStage, $usrStageSession);

        $this->consumeLapStaminaCost($usrUserId, $mstStage, $now, $oprCampaigns, $lapCount);

        $beforeClearTimeMs = $usrStage->getClearTimeMs();
        $usrStage->setClearTimeMs($inGameBattleLogData->getClearTimeMs());
        $this->clear($usrUserId, $mstStage, $usrStage, $usrStageSession, $inGameBattleLogData, $partyNo, $lapCount);

        $this->applyLapExtras($usrUserId, $mstStage, $partyNo, $lapCount);

        // 各種報酬配布
        if ($this->stageService->isSpeedAttack($mstStageId, $now)) {
            $this->addMstStageClearTimeRewards(
                $mstStageId,
                $beforeClearTimeMs,
                $inGameBattleLogData->getClearTimeMs(),
            );
        }
        $this->addMstStageRewards($mstStage, $usrStage, $oprCampaigns, $lapCount);
        $this->acquireArtworkAndArtworkFragments($usrUserId, $mstStage, $oprCampaigns, $lapCount);
    }
}

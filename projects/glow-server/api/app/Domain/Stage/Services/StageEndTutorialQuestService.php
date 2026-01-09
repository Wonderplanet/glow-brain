<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Repositories\MstStageClearTimeRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\UsrStageInterface;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;

/**
 * チュートリアルクエストのステージのクリア処理を行うサービスクラス
 */
class StageEndTutorialQuestService extends StageEndQuestService
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
            QuestType::TUTORIAL
        );
    }

    public function endTutorial(
        string $usrUserId,
        MstStageEntity $mstStage,
    ): void {
        $mstStageId = $mstStage->getId();

        /** @var UsrStageInterface $usrStage */
        $usrStage = $this->usrStageRepository->findByMstStageId($usrUserId, $mstStageId);
        /** @var UsrStageSessionInterface $usrStageSession */
        $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);

        $this->validateCanEnd($mstStage, $usrStage, $usrStageSession);

        $this->clearTutorial($mstStage, $usrStage, $usrStageSession);

        $this->addMstStageRewards(
            $mstStage,
            $usrStage,
            collect(),
            1,
        );
    }

    private function clearTutorial(
        MstStageEntity $mstStage,
        UsrStageInterface $usrStage,
        UsrStageSessionInterface $usrStageSession,
    ): void {
        $usrStageSession->closeSession();
        $this->usrStageSessionRepository->syncModel($usrStageSession);

        $usrStage->incrementClearCount();
        $this->usrStageRepository->syncModel($usrStage);
    }
}

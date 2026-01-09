<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Repositories\UsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;

class StageStartNormalQuestService extends StageStartQuestService
{
    public function __construct(
        // 抽象
        protected UsrStageRepository $usrStageNormalRepository,
        // Repository
        protected MstStageRepository $mstStageRepository,
        protected OprCampaignRepository $oprCampaignRepository,
        protected UsrStageSessionRepository $usrStageSessionRepository,
        // Service
        protected StageService $stageService,
        protected StageMissionTriggerService $stageMissionTriggerService,
        protected StageLogService $stageLogService,
        // Delegator
        protected RewardDelegator $rewardDelegator,
        protected UserDelegator $userDelegator,
        protected UnitDelegator $unitDelegator,
        protected InGameDelegator $inGameDelegator,
    ) {
        parent::__construct(
            $usrStageNormalRepository,
            $mstStageRepository,
            $oprCampaignRepository,
            $usrStageSessionRepository,
            $stageService,
            $stageMissionTriggerService,
            $stageLogService,
            $rewardDelegator,
            $userDelegator,
            $unitDelegator,
            $inGameDelegator,
        );
    }
}

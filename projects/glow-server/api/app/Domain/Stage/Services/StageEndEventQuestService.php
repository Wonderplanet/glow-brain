<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Mst\Repositories\MstStageClearTimeRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Repositories\UsrStageEventRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;

class StageEndEventQuestService extends StageEndQuestService
{
    public function __construct(
        // 具象
        protected UsrStageEventRepository $usrStageEventRepository,
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
            $usrStageEventRepository,
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
            QuestType::EVENT
        );
    }
}

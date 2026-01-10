<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Repositories\MstStageClearTimeRewardRepository;
use App\Domain\Resource\Mst\Repositories\MstStageEnhanceRewardParamRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Entities\StageInGameBattleLog;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageEnhanceRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageEndEnhanceQuestService extends StageEndQuestService
{
    public function __construct(
        // 具象
        protected UsrStageEnhanceRepository $usrStageEnhanceRepository,
        // MstRepository
        protected MstStageRepository $mstStageRepository,
        private MstStageEnhanceRewardParamRepository $mstStageEnhanceRewardParamRepository,
        protected MstStageClearTimeRewardRepository $mstStageClearTimeRewardRepository,
        // UsrRepository
        protected UsrStageSessionRepository $usrStageSessionRepository,
        // Service
        protected StageService $stageService,
        protected StageMissionTriggerService $stageMissionTriggerService,
        protected StageLogService $stageLogService,
        protected QuestBonusUnitService $questBonusUnitService,
        // Delegator
        protected UserDelegator $userDelegator,
        protected UnitDelegator $unitDelegator,
        protected RewardDelegator $rewardDelegator,
        protected EncyclopediaDelegator $encyclopediaDelegator,
        protected PartyDelegator $partyDelegator,
    ) {
        parent::__construct(
            $usrStageEnhanceRepository,
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
            QuestType::ENHANCE
        );
    }

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

        $usrStage = $this->usrStageRepository->findByMstStageId($usrUserId, $mstStageId);
        $partyNo = $usrStageSession->getPartyNo();

        $this->validateCanEnd($mstStage, $usrStage, $usrStageSession);

        $this->clear($usrUserId, $mstStage, $usrStage, $usrStageSession, $inGameBattleLogData, $partyNo, $lapCount);

        $this->applyLapExtras($usrUserId, $mstStage, $partyNo, $lapCount);

        // ステージ共通報酬と、原画かけらドロップは、なし

        $score = $inGameBattleLogData->getScore();

        // ハイスコアとスタミナブースト時の追加コスト保存
        $usrStageEnhance = $this->usrStageEnhanceRepository->findByMstStageId($usrUserId, $mstStageId);
        $usrStageEnhance->setMaxScore($score);
        if ($lapCount > 1) {
            $usrStageEnhance->addResetChallengeCount($lapCount - 1);
        }
        $this->usrStageEnhanceRepository->syncModel($usrStageEnhance);

        $this->addCoinRewards($usrUserId, $mstStage, $oprCampaigns, $score, $partyNo, $now, $lapCount);
    }

    /**
     * 強化クエスト専用のコイン報酬を算出し、配布リストへ追加する
     */
    public function addCoinRewards(
        string $usrUserId,
        MstStageEntity $mstStage,
        Collection $oprCampaigns,
        int $score,
        int $partyNo,
        CarbonImmutable $now,
        int $lapCount,
    ): void {
        $rewards = collect();

        $mstStageEnhanceRewardParam = $this->mstStageEnhanceRewardParamRepository->getByMinThresholdScoreUnder($score);

        // 強化クエストの報酬パラメータが存在しない場合は、何もしない
        if (is_null($mstStageEnhanceRewardParam)) {
            return;
        }

        $coinAmount = $mstStageEnhanceRewardParam->getCoinRewardAmount();

        // ユニットボーナス適用
        $party = $this->partyDelegator->getParty($usrUserId, $partyNo);
        $mstUnitIds = $party->getMstUnitIds();
        $coinBonusRate = $this->questBonusUnitService->getCoinBonusRate(
            $mstStage->getMstQuestId(),
            $mstUnitIds,
            $now,
        );
        $bonusCoinAmount = (int)ceil($coinAmount * $coinBonusRate);

        // キャンペーン適用
        $coinAmount = $this->stageService->applyCampaignByRewardType(
            $oprCampaigns,
            RewardType::COIN->value,
            $coinAmount,
        );

        $totalCoinAmount = $coinAmount + $bonusCoinAmount;
        $totalCoinAmount *= $lapCount;

        $rewards->push(
            // 強化クエストの定常報酬として扱う
            new StageAlwaysClearReward(
                RewardType::COIN->value,
                null,
                $totalCoinAmount,
                $mstStage->getId(),
                $oprCampaigns->get(CampaignType::COIN_DROP->value)?->getEffectValue(),
            )
        );

        $this->rewardDelegator->addRewards($rewards);
    }
}

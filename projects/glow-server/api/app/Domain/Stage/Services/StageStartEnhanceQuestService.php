<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Mst\Entities\MstQuestEntity;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Models\UsrStageEnhanceInterface;
use App\Domain\Stage\Repositories\UsrStageEnhanceRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageStartEnhanceQuestService extends StageStartQuestService
{
    public function __construct(
        // 抽象
        protected UsrStageEnhanceRepository $usrStageEnhanceRepository,
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
        protected PartyDelegator $partyDelegator,
        // Common
        private Clock $clock,
        private MstConfigService $mstConfigService,
    ) {
        parent::__construct(
            $usrStageEnhanceRepository,
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

    public function start(
        string $usrUserId,
        int $partyNo,
        MstStageEntity $mstStage,
        MstQuestEntity $mstQuest,
        bool $isChallengeAd,
        int $lapCount,
        CarbonImmutable $now,
    ): void {
        $mstStageId = $mstStage->getId();

        $oprCampaigns = $this->oprCampaignRepository->getActivesByMstQuest(
            $now,
            $mstQuest,
        );

        // コスト消費なし

        /** @var null|UsrStageEnhanceInterface $usrStage */
        $usrStage = $this->unlockStage($usrUserId, $mstStage, $now);
        if (is_null($usrStage)) {
            throw new GameException(
                ErrorCode::STAGE_CANNOT_START,
                sprintf('not found in usr_stages (mst_stage_id: %s)', $mstStage->getId()),
            );
        }

        // スタミナブーストチェック
        $this->stageService->validateCanAutoLap(
            $mstStage,
            $usrStage,
            $isChallengeAd,
            $lapCount,
        );

        $this->startSession(
            $usrUserId,
            $now,
            $mstStage,
            $mstQuest,
            $partyNo,
            $oprCampaigns,
            $isChallengeAd,
            $lapCount
        );

        // ユニットの出撃回数更新
        $party = $this->partyDelegator->getParty($usrUserId, $partyNo);
        $this->unitDelegator->incrementBattleCount($usrUserId, $party->getUsrUnitIds());

        // ステージ進捗リセット
        if ($this->isNeedReset($usrStage)) {
            $usrStage->reset($now);
        }

        // 回数制限を確認。確認するタイミングは必ずリセットの後。
        $this->validateCanStart(
            $usrStage,
            $isChallengeAd,
            $oprCampaigns,
            $lapCount,
        );

        $usrStage->incrementChallengeCount($isChallengeAd);

        // ステージステータスを更新
        $this->usrStageRepository->syncModel($usrStage);

        // ミッショントリガー送信
        $this->stageMissionTriggerService->sendStageStartTriggers(
            $usrUserId,
            $mstStageId,
            $partyNo,
        );
    }

    protected function isNeedReset(
        UsrStageEnhanceInterface $usrStage,
    ): bool {
        $latestResetAt = $usrStage->getLatestResetAt();

        return $this->clock->isFirstToday($latestResetAt);
    }

    /**
     * ステージに挑戦できる状態かどうかを確認する
     * 挑戦できない場合はエラーを投げる
     *
     * @param \Illuminate\Support\Collection<OprCampaignEntity> $oprCampaigns
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function validateCanStart(
        UsrStageEnhanceInterface $usrStage,
        bool $isChallengeAd,
        Collection $oprCampaigns,
        int $lapCount,
    ): void {
        $adChallengeCountLimit = $this->mstConfigService->getEnhanceQuestChallengeAdLimit();
        if ($isChallengeAd) {
            // 広告視聴で挑戦した場合
            if ($adChallengeCountLimit <= $usrStage->getResetAdChallengeCount()) {
                throw new GameException(
                    ErrorCode::STAGE_CANNOT_START,
                    'ad challenge count is over',
                );
            }
        } else {
            // 通常挑戦した場合
            $challengeCountLimit = $this->calcChallengeCountLimit($oprCampaigns);
            if ($challengeCountLimit <= $usrStage->getResetChallengeCount()) {
                throw new GameException(
                    ErrorCode::STAGE_CANNOT_START,
                    'challenge count is over',
                );
            }
            if (($lapCount > 1) && ($challengeCountLimit < ($usrStage->getResetChallengeCount() + $lapCount))) {
                throw new GameException(
                    ErrorCode::STAGE_CAN_NOT_AUTO_LAP_CHALLENGE_LIMIT,
                    'challenge count add auto lap is over',
                );
            }
        }
    }

    /**
     * 通常挑戦可能な回数上限を計算する
     *
     * @param \Illuminate\Support\Collection<OprCampaignEntity> $oprCampaigns
     * @return int
     */
    protected function calcChallengeCountLimit(
        Collection $oprCampaigns,
    ): int {
        /** @var ?OprCampaignEntity $oprCampaign */
        $oprCampaign = $oprCampaigns->filter(function (OprCampaignEntity $oprCampaign) {
            return $oprCampaign->isChallengeCountCampaign();
        })->first();
        $addCampaignChallengeCountLimit = $oprCampaign?->getChallengeCountEffectValue() ?? 0;

        $addMstConfigCountLimit = $this->mstConfigService->getEnhanceQuestChallengeLimit();

        return $addMstConfigCountLimit + $addCampaignChallengeCountLimit;
    }
}

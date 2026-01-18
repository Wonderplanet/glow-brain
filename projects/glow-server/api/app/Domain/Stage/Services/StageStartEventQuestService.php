<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Resource\Mst\Entities\MstQuestEntity;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Entities\MstStageEventSettingEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\MstStageEventSettingRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Enums\StageResetType;
use App\Domain\Stage\Models\UsrStageEventInterface;
use App\Domain\Stage\Repositories\UsrStageEventRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageStartEventQuestService extends StageStartQuestService
{
    public function __construct(
        // 抽象
        protected UsrStageEventRepository $usrStageEventRepository,
        // Repository
        protected MstStageRepository $mstStageRepository,
        protected OprCampaignRepository $oprCampaignRepository,
        protected UsrStageSessionRepository $usrStageSessionRepository,
        private MstStageEventSettingRepository $mstStageEventSettingRepository,
        // Service
        protected StageService $stageService,
        protected StageMissionTriggerService $stageMissionTriggerService,
        protected StageLogService $stageLogService,
        // Delegator
        protected RewardDelegator $rewardDelegator,
        protected UserDelegator $userDelegator,
        protected UnitDelegator $unitDelegator,
        protected InGameDelegator $inGameDelegator,
        // Common
        private Clock $clock,
    ) {
        parent::__construct(
            $usrStageEventRepository,
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

        $party = $this->checkPartyRules(
            $usrUserId,
            $partyNo,
            $mstStageId,
            $now,
        );

        $oprCampaigns = $this->oprCampaignRepository->getActivesByMstQuest(
            $now,
            $mstQuest,
        );

        /** @var MstStageEventSettingEntity $mstStageEventSetting */
        $mstStageEventSetting = $this->mstStageEventSettingRepository->getActiveByMstStageId(
            $mstStageId,
            $now,
            isThrowError: true,
        );

        // 広告視聴時はコスト消費をスキップ
        if (!$isChallengeAd) {
            $this->consumeCost(
                $usrUserId,
                $mstStage,
                $now,
                $oprCampaigns,
                $lapCount,
            );
        }

        /** @var null|UsrStageEventInterface $usrStage */
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
            $lapCount,
        );

        // ユニットの出撃回数更新
        $this->unitDelegator->incrementBattleCount($usrUserId, $party->getUsrUnitIds());

        // ミッショントリガー送信
        $this->stageMissionTriggerService->sendStageStartTriggers(
            $usrUserId,
            $mstStageId,
            $partyNo,
        );

        $usrStage->setLastChallengedAt($now->toDateTimeString());

        if ($mstStageEventSetting->hasUnlimitedClearableCount()) {
            // クリア可能回数が無制限の場合はリセット関連処理は行わない
            // last_challenged_at用にステージステータスを更新
            $this->usrStageRepository->syncModel($usrStage);
            return;
        }

        // ステージ進捗リセット
        if ($this->isNeedReset($mstStageEventSetting, $usrStage)) {
            $usrStage->reset($now);
        }

        // 回数制限を確認。確認するタイミングは必ずリセットの後。
        $this->validateCanStart(
            $mstStageEventSetting,
            $usrStage,
            $isChallengeAd,
            $oprCampaigns,
            $lapCount,
        );

        if ($isChallengeAd) {
            $usrStage->incrementResetAdChallengeCount();
        }

        // ステージステータスを更新
        $this->usrStageRepository->syncModel($usrStage);
    }

    protected function isNeedReset(
        MstStageEventSettingEntity $mstStageEventSetting,
        UsrStageEventInterface $usrStage,
    ): bool {
        $latestResetAt = $usrStage->getLatestResetAt();

        return match ($mstStageEventSetting->getResetType()) {
            StageResetType::DAILY->value => $this->clock->isFirstToday($latestResetAt),
            default => false,
        };
    }

    /**
     * ステージに挑戦できる状態かどうかを確認する
     * 挑戦できない場合はエラーを投げる
     *
     * @param \Illuminate\Support\Collection<OprCampaignEntity> $oprCampaigns
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function validateCanStart(
        MstStageEventSettingEntity $mstStageEventSetting,
        UsrStageEventInterface $usrStage,
        bool $isChallengeAd,
        Collection $oprCampaigns,
        int $lapCount,
    ): void {
        $clearableCountLimit = $this->calcClearableCountLimit($mstStageEventSetting, $oprCampaigns);

        $adChallengeCountLimit = $mstStageEventSetting->getAdChallengeCount();
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
            if ($clearableCountLimit <= $usrStage->getResetClearCount()) {
                throw new GameException(
                    ErrorCode::STAGE_CANNOT_START,
                    'clearable count is over',
                );
            }
            if (($lapCount > 1) && ($clearableCountLimit < ($usrStage->getResetClearCount() + $lapCount))) {
                throw new GameException(
                    ErrorCode::STAGE_CAN_NOT_AUTO_LAP_CHALLENGE_LIMIT,
                    'challenge count add auto lap is over',
                );
            }
        }
    }

    /**
     * ステージに挑戦可能な回数上限を計算する
     *
     * @param \Illuminate\Support\Collection<OprCampaignEntity> $oprCampaigns
     * @return int
     */
    protected function calcClearableCountLimit(
        MstStageEventSettingEntity $mstStageEventSetting,
        Collection $oprCampaigns,
    ): int {
        /** @var ?OprCampaignEntity $oprCampaign */
        $oprCampaign = $oprCampaigns->filter(function (OprCampaignEntity $oprCampaign) {
            return $oprCampaign->isChallengeCountCampaign();
        })->first();
        $addCampaignClearableCountLimit = $oprCampaign?->getChallengeCountEffectValue() ?? 0;

        $addEventClearableCountLimit = $mstStageEventSetting->getClearableCount();

        return $addCampaignClearableCountLimit + $addEventClearableCountLimit;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Resource\Entities\LogTriggers\StageChallengeLogTrigger;
use App\Domain\Resource\Entities\Party;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Entities\MstQuestEntity;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Stage\Models\IBaseUsrStage;
use App\Domain\Stage\Repositories\IUsrStageRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StageStartQuestService
{
    public function __construct(
        // 抽象
        protected IUsrStageRepository $usrStageRepository,
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

        $this->consumeCost(
            $usrUserId,
            $mstStage,
            $now,
            $oprCampaigns,
            $lapCount,
        );

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
        $this->unitDelegator->incrementBattleCount($usrUserId, $party->getUsrUnitIds());

        // ミッショントリガー送信
        $this->stageMissionTriggerService->sendStageStartTriggers(
            $usrUserId,
            $mstStageId,
            $partyNo,
        );
    }

    public function consumeCost(
        string $usrUserId,
        MstStageEntity $mstStage,
        CarbonImmutable $now,
        Collection $oprCampaigns,
        int $lapCount,
    ): void {

        $stageStaminaCost = $this->stageService->calcStaminaCost(
            $mstStage,
            $oprCampaigns,
            $lapCount,
        );

        // 周回を考慮したスタミナ消費確認をする
        $this->userDelegator->validateStamina($usrUserId, $stageStaminaCost->getLapStaminaCost(), $now);

        $this->userDelegator->consumeStamina(
            $usrUserId,
            $stageStaminaCost->getStaminaCost(),
            $now,
            new StageChallengeLogTrigger($mstStage->getId(), $lapCount),
        );
    }

    /**
     * ステージ開放処理
     *
     * @return IBaseUsrStage|null 開放されたステージ。nullの場合は、開放されなかったことを示す
     */
    public function unlockStage(
        string $usrUserId,
        MstStageEntity $mstStage,
        CarbonImmutable $now,
    ): ?IBaseUsrStage {
        $mstStageId = $mstStage->getId();
        $prevMstStageId = $mstStage->getPrevMstStageId();

        $mstStageIds = collect([$mstStageId]);
        if (!is_null($prevMstStageId)) {
            $mstStageIds->push($prevMstStageId);
        }
        $usrStages = $this->usrStageRepository->findByMstStageIds($usrUserId, $mstStageIds);

        $usrStage = $usrStages->get($mstStageId);
        $prevUsrStage = $usrStages->get($prevMstStageId);

        // 既に開放済みなら何もしない
        if (!is_null($usrStage)) {
            return $usrStage;
        }

        // 未開放で初期開放ステージなら開放して返す
        if (is_null($prevMstStageId)) {
            return $this->usrStageRepository->create($usrUserId, $mstStageId, $now);
        }

        // 未開放で、初期開放ステージではないが、前ステージが未開放なら何もしない
        if (is_null($prevUsrStage)) {
            return null;
        }

        // 未開放で前ステージがクリア済みなら開放して返す
        if ($prevUsrStage->isClear()) {
            return $this->usrStageRepository->create($usrUserId, $mstStageId, $now);
        }

        return null;
    }

    /**
     * ステージセッション開始処理
     *
     * @param Collection<OprCampaignEntity> $oprCampaigns
     */
    public function startSession(
        string $usrUserId,
        CarbonImmutable $now,
        MstStageEntity $mstStage,
        MstQuestEntity $mstQuest,
        int $partyNo,
        Collection $oprCampaigns,
        bool $isChallengeAd,
        int $lapCount,
    ): void {
        $mstStageId = $mstStage->getId();

        $oprCampaignIds = $oprCampaigns->map(fn(OprCampaignEntity $entity) => $entity->getId());

        $usrStageSession = $this->usrStageSessionRepository->get($usrUserId, $now);
        // api/stage/startAPIを複数回連続で実行しても問題ないので上書きする
        $usrStageSession->startSession($mstStageId, $partyNo, $oprCampaignIds, $isChallengeAd, $lapCount);
        $this->usrStageSessionRepository->syncModel($usrStageSession);
    }

    /**
     * ステージ挑戦時に、指定パーティのユニットがルールに適合しているか確認
     * ルールに適合しない場合はエラー
     *
     * @param string $usrUserId
     * @param int $partyNo
     * @param string $mstStageId
     * @param \Carbon\CarbonImmutable $now
     * @return Party
     */
    public function checkPartyRules(
        string $usrUserId,
        int $partyNo,
        string $mstStageId,
        CarbonImmutable $now,
    ): Party {
        return $this->inGameDelegator->checkAndGetParty(
            $usrUserId,
            $partyNo,
            InGameContentType::STAGE,
            $mstStageId,
            $now,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Stage\Delegators\StageDelegator;
use App\Domain\Unit\Delegators\UnitDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AdventBattleStartService
{
    public function __construct(
        // Repository
        protected UsrAdventBattleRepository $usrAdventBattleRepository,
        protected UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
        protected OprCampaignRepository $oprCampaignRepository,
        // Service
        protected AdventBattleService $adventBattleService,
        protected AdventBattleLogService $adventBattleLogService,
        private readonly AdventBattleMissionTriggerService $adventBattleMissionTriggerService,
        // Delegator
        protected StageDelegator $stageDelegator,
        protected PartyDelegator $partyDelegator,
        protected UnitDelegator $unitDelegator,
        protected InGameDelegator $inGameDelegator,
    ) {
    }

    /**
     * 開始
     * @param string $usrUserId
     * @param int $partyNo
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param bool $isChallengeAd
     * @param CarbonImmutable $now
     * @return UsrAdventBattleInterface
     * @throws GameException
     * @throws \Throwable
     */
    public function start(
        string $usrUserId,
        int $partyNo,
        MstAdventBattleEntity $mstAdventBattle,
        bool $isChallengeAd,
        CarbonImmutable $now,
    ): UsrAdventBattleInterface {
        $mstAdventBattleId = $mstAdventBattle->getId();
        $usrAdventBattle = $this->adventBattleService->fetchAndResetAdventBattleByAdventBattleId(
            $usrUserId,
            $mstAdventBattleId,
            $now,
        );

        $oprCampaigns = $this->oprCampaignRepository->getActivesByMstAdventBattleId(
            $now,
            $mstAdventBattleId,
        );

        $this->validateCanStart($mstAdventBattle, $usrAdventBattle, $isChallengeAd, $oprCampaigns);

        $party = $this->inGameDelegator->checkAndGetParty(
            $usrUserId,
            $partyNo,
            InGameContentType::ADVENT_BATTLE,
            $mstAdventBattleId,
            $now,
        );

        $this->startSession(
            $usrUserId,
            $mstAdventBattle,
            $partyNo,
            $now,
            $isChallengeAd,
        );

        $this->adventBattleMissionTriggerService->sendStartTriggers();

        // ログ送信
        $this->adventBattleLogService->sendStartLog(
            $usrUserId,
            $mstAdventBattleId,
            $partyNo,
            $now,
            $isChallengeAd,
        );

        // 挑戦回数更新
        $usrAdventBattle->incrementChallengeCount($isChallengeAd);
        $this->usrAdventBattleRepository->syncModel($usrAdventBattle);

        // ユニットの出撃回数更新
        $this->unitDelegator->incrementBattleCount($usrUserId, $party->getUsrUnitIds());

        return $usrAdventBattle;
    }

    /**
     * 降臨バトルに挑戦できる状態かどうかを確認する
     *
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param bool $isChallengeAd
     * @param Collection $oprCampaigns
     * @return void
     * @throws GameException
     */
    public function validateCanStart(
        MstAdventBattleEntity $mstAdventBattle,
        UsrAdventBattleInterface $usrAdventBattle,
        bool $isChallengeAd,
        Collection $oprCampaigns,
    ): void {
        if ($isChallengeAd) {
            // 広告視聴で挑戦した場合
            $adChallengeableCountLimit = $mstAdventBattle->getAdChallengeableCount();
            if ($adChallengeableCountLimit <= $usrAdventBattle->getResetAdChallengeCount()) {
                throw new GameException(
                    ErrorCode::ADVENT_BATTLE_CANNOT_START,
                    'ad challengeable count is over',
                );
            }
        } else {
            // 通常挑戦した場合
            $challengeableCountLimit = $this->calcClearableCountLimit($mstAdventBattle, $oprCampaigns);
            if ($challengeableCountLimit <= $usrAdventBattle->getResetChallengeCount()) {
                throw new GameException(
                    ErrorCode::ADVENT_BATTLE_CANNOT_START,
                    'challengeable count is over',
                );
            }
        }
    }

    /**
     * 降臨バトルに挑戦可能な回数上限を計算する
     *
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param Collection $oprCampaigns
     * @return int
     */
    protected function calcClearableCountLimit(
        MstAdventBattleEntity $mstAdventBattle,
        Collection $oprCampaigns,
    ): int {
        /** @var ?OprCampaignEntity $oprCampaign */
        $oprCampaign = $oprCampaigns->filter(function (OprCampaignEntity $oprCampaign) {
            return $oprCampaign->isChallengeCountCampaign();
        })->first();
        $addCampaignClearableCountLimit = $oprCampaign?->getChallengeCountEffectValue() ?? 0;

        $addEventClearableCountLimit = $mstAdventBattle->getChallengeableCount();

        return $addCampaignClearableCountLimit + $addEventClearableCountLimit;
    }

    /**
     * 降臨バトルセッション開始処理
     *
     * @param string $usrUserId
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param int $partyNo
     */
    public function startSession(
        string $usrUserId,
        MstAdventBattleEntity $mstAdventBattle,
        int $partyNo,
        CarbonImmutable $now,
        bool $isChallengeAd,
    ): void {
        $mstAdventBattleId = $mstAdventBattle->getId();
        $usrAdventBattleSession = $this->usrAdventBattleSessionRepository->findOrCreate($usrUserId, $now);
        $usrAdventBattleSession->startSession($mstAdventBattleId, $partyNo, $now, $isChallengeAd);
        $this->usrAdventBattleSessionRepository->syncModel($usrAdventBattleSession);
    }
}

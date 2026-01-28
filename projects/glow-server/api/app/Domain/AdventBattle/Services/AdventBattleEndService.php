<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Entities\Rewards\AdventBattleDropReward;
use App\Domain\Resource\Entities\Unit;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity;
use App\Domain\Reward\Delegators\RewardDelegator;

abstract class AdventBattleEndService
{
    public function __construct(
        // Service
        protected readonly AdventBattleLogService $adventBattleLogService,
        protected readonly AdventBattleCacheService $adventBattleCacheService,
        private readonly AdventBattleRewardRankService $adventBattleRewardRankService,
        private readonly AdventBattleMissionTriggerService $adventBattleMissionTriggerService,
        private readonly AdventBattleClearRewardService $adventBattleClearRewardService,
        // Repository
        protected readonly UsrAdventBattleRepository $usrAdventBattleRepository,
        protected readonly UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
        // Delegator
        protected RewardDelegator $rewardDelegator,
        protected PartyDelegator $partyDelegator,
    ) {
    }

    /**
     * 降臨バトル終了処理
     * @param string $usrUserId
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param UsrAdventBattleInterface $usrAdventBattle,
     * @param UsrAdventBattleSessionInterface $usrAdventBattleSession
     * @param AdventBattleInGameBattleLog $inGameBattleLogData
     * @throws GameException
     */
    public function end(
        string $usrUserId,
        MstAdventBattleEntity $mstAdventBattle,
        UsrAdventBattleInterface $usrAdventBattle,
        UsrAdventBattleSessionInterface $usrAdventBattleSession,
        AdventBattleInGameBattleLog $inGameBattleLogData,
    ): void {
        $mstAdventBattleId = $mstAdventBattle->getId();
        $partyNo = $usrAdventBattleSession->getPartyNo();

        $this->validateCanEnd($mstAdventBattle, $usrAdventBattle, $usrAdventBattleSession);

        $this->endSession($usrAdventBattleSession);

        $this->updateScore($usrAdventBattle, $inGameBattleLogData, $partyNo);

        $this->addAdventBattleDropRewards($mstAdventBattle);

        $this->addAdventBattleClearRewards($usrAdventBattle);

        $this->addAdventBattleRankRewards($usrAdventBattle);

        $this->adventBattleMissionTriggerService->sendEndTriggers($inGameBattleLogData);

        // ログ送信
        $this->adventBattleLogService->sendEndLog($usrUserId, $mstAdventBattleId, $partyNo, $inGameBattleLogData);
    }

    /**
     * 降臨バトルが終了できる状態かバリデーション
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param UsrAdventBattleInterface|null $usrAdventBattle
     * @param UsrAdventBattleSessionInterface $usrAdventBattleSession
     * @throws GameException
     */
    private function validateCanEnd(
        MstAdventBattleEntity $mstAdventBattle,
        ?UsrAdventBattleInterface $usrAdventBattle,
        UsrAdventBattleSessionInterface $usrAdventBattleSession,
    ): void {
        if (
            !$usrAdventBattleSession->isStartedByMstAdventBattleId($mstAdventBattle->getId())
            || is_null($usrAdventBattle)
        ) {
            throw new GameException(ErrorCode::ADVENT_BATTLE_SESSION_MISMATCH);
        }
    }

    /**
     * 降臨バトルセッション終了処理
     * @param UsrAdventBattleSessionInterface $usrAdventBattleSession
     */
    private function endSession(
        UsrAdventBattleSessionInterface $usrAdventBattleSession,
    ): void {
        $usrAdventBattleSession->closeSession();
        $this->usrAdventBattleSessionRepository->syncModel($usrAdventBattleSession);
    }

    /**
     * 降臨バトルのスコア更新の実行
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param AdventBattleInGameBattleLog $inGameBattleLogData
     * @param int $partyNo
     * @return void
     */
    protected function updateScore(
        UsrAdventBattleInterface $usrAdventBattle,
        AdventBattleInGameBattleLog $inGameBattleLogData,
        int $partyNo,
    ): void {
        $score = $inGameBattleLogData->getScore();
        $updateRankingScore = null;
        if ($usrAdventBattle->getMaxScore() < $score) {
            $partyData = $this
                ->partyDelegator
                ->getParty($usrAdventBattle->getUsrUserId(), $partyNo)
                ->getUnits()
                ->map(fn (Unit $unit) => $unit->getUsrUnit()->formatToLog());

            $usrAdventBattle->setMaxScoreParty($partyData->toArray());
            $usrAdventBattle->setMaxScore($score);

            $updateRankingScore = $score;
        }

        // チート判定されている場合はランキング更新用のスコアを上書き
        // DBに保存するスコアについては受け取った値で更新する
        $updateRankingScore = $usrAdventBattle->isExcludedRanking()
            ? AdventBattleConstant::RANKING_CHEATER_SCORE
            : $updateRankingScore;

        if ($updateRankingScore !== null) {
            // ハイスコアまたはチート判定された場合はランキングのスコアを更新
            $this->adventBattleCacheService->addRankingScore(
                $usrAdventBattle->getMstAdventBattleId(),
                $usrAdventBattle->getUsrUserId(),
                $updateRankingScore
            );
        }

        $usrAdventBattle->setTotalScore($usrAdventBattle->getTotalScore() + $score);
        $usrAdventBattle->incrementClearCount();
        $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
    }

    /**
     * 降臨バトルのドロップ報酬を配布リストへ追加する
     *
     * @param MstAdventBattleEntity $mstAdventBattle
     */
    public function addAdventBattleDropRewards(
        MstAdventBattleEntity $mstAdventBattle,
    ): void {
        $sendRewards = collect();

        // 仕様上、ドロップ報酬はユーザーEXP、コインだけとなっているためマスタから取得して設定
        $sendRewards->push(
            new AdventBattleDropReward(
                RewardType::COIN->value,
                null,
                $mstAdventBattle->getCoin(),
                $mstAdventBattle->getId(),
            )
        );
        $sendRewards->push(
            new AdventBattleDropReward(
                RewardType::EXP->value,
                null,
                $mstAdventBattle->getExp(),
                $mstAdventBattle->getId(),
            )
        );

        $this->rewardDelegator->addRewards($sendRewards);
    }

    /**
     * 降臨バトルのクリア報酬を配布リストへ追加する
     *
     * @param UsrAdventBattleInterface $usrAdventBattle
     */
    public function addAdventBattleClearRewards(
        UsrAdventBattleInterface $usrAdventBattle,
    ): void {
        $sendRewards = collect();

        // 初回クリア報酬
        if ($usrAdventBattle->isFirstClear()) {
            $sendRewards = $sendRewards->merge($this->adventBattleClearRewardService->getFirstClearRewards(
                $usrAdventBattle->getMstAdventBattleId()
            ));
        }
        // 定常クリア報酬
        $sendRewards = $sendRewards->merge($this->adventBattleClearRewardService->getAlwaysClearRewards(
            $usrAdventBattle->getMstAdventBattleId()
        ));

        // ランダムクリア報酬
        $sendRewards = $sendRewards->merge($this->adventBattleClearRewardService->getRandomClearRewards(
            $usrAdventBattle->getMstAdventBattleId()
        ));

        $this->rewardDelegator->addRewards($sendRewards);
    }

    /**
     * 降臨バトルのランク報酬を配布リストへ追加する
     *
     * @param UsrAdventBattleInterface $usrAdventBattle
     */
    public function addAdventBattleRankRewards(
        UsrAdventBattleInterface $usrAdventBattle,
    ): void {
        $adventBattleReceivableReward = $this->adventBattleRewardRankService->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();
        if ($mstAdventBattleRewards->isEmpty()) {
            return;
        }

        $adventBattleRewards = $this->adventBattleRewardRankService->convertRewards(
            $usrAdventBattle->getMstAdventBattleId(),
            $mstAdventBattleRewards
        );
        $this->rewardDelegator->addRewards($adventBattleRewards);

        // どの報酬まで付与したか保存
        $this->adventBattleRewardRankService->setLatestReceivedRewardGroupId(
            $usrAdventBattle,
            $adventBattleReceivableReward->getLatestMstAdventBattleRewardGroupId()
        );
    }

    /**
     * 降臨バトルの全ユーザー累計スコア更新の実行
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param AdventBattleInGameBattleLog $inGameBattleLogData
     * @return int
     */
    abstract public function updateAllUserTotalScore(
        MstAdventBattleEntity $mstAdventBattle,
        AdventBattleInGameBattleLog $inGameBattleLogData,
    ): int;
}

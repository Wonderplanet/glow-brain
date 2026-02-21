<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\AdventBattle\Enums\LogAdventBattleResult;
use App\Domain\AdventBattle\Repositories\LogAdventBattleActionRepository;
use App\Domain\AdventBattle\Repositories\LogAdventBattleRewardRepository;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;
use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Services\AdPlayService;
use App\Domain\Common\Services\CommonInGameService;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Party\Delegators\ArtworkPartyDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Entities\Rewards\AdventBattleAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleDropReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleFirstClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleMaxScoreReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRaidTotalScoreReward;
use App\Domain\Resource\Entities\Unit;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;

/**
 * 降臨バトルログサービス
 */
class AdventBattleLogService
{
    public function __construct(
        // Repository
        private UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
        private LogAdventBattleActionRepository $logAdventBattleActionRepository,
        private LogAdventBattleRewardRepository $logAdventBattleRewardRepository,
        // Delegator
        private PartyDelegator $partyDelegator,
        private ArtworkPartyDelegator $artworkPartyDelegator,
        private OutpostDelegator $outpostDelegator,
        private RewardDelegator $rewardDelegator,
        // Common
        private CommonInGameService $commonInGameService,
        private AdPlayService $adPlayService,
    ) {
    }

    /**
     * インゲームバトルログデータを生成する
     *
     * @param array<array<mixed>>  $inGameBattleLog
     * @return AdventBattleInGameBattleLog
     */
    public function makeInGameBattleLogData(array $inGameBattleLog): AdventBattleInGameBattleLog
    {
        return new AdventBattleInGameBattleLog(
            $inGameBattleLog['defeatEnemyCount'] ?? 0,
            $inGameBattleLog['defeatBossEnemyCount'] ?? 0,
            $inGameBattleLog['score'] ?? 0,
            $this->partyDelegator->makePartyStatusList(collect($inGameBattleLog['partyStatus'] ?? [])),
            $inGameBattleLog['maxDamage'] ?? 0,
            $this->commonInGameService->makeDiscoveredEnemyDataList($inGameBattleLog['discoveredEnemies'] ?? []),
            $this->artworkPartyDelegator->makeArtworkPartyStatusList(
                collect($inGameBattleLog['artworkPartyStatus'] ?? [])
            ),
        );
    }

    /**
     * パーティユニットを取得する
     *
     * @param string $usrUserId
     * @param int|null $partyNo
     * @return array<mixed>|null
     */
    private function getPartyUnits(string $usrUserId, ?int $partyNo): ?array
    {
        if (is_null($partyNo)) {
            return null;
        }

        return $this->partyDelegator
            ->getParty($usrUserId, $partyNo)
            ->getUnits()
            ->map(
                fn(Unit $unit) => $unit->getUsrUnit()->formatToLog()
            )->toArray();
    }

    /**
     * 使用したゲート情報を取得する
     *
     * @param string $usrUserId
     * @return array<mixed>|null
     */
    private function getUsedOutpost(string $usrUserId): ?array
    {
        $usedOutpost = $this->outpostDelegator->getUsedOutpost($usrUserId);
        return $usedOutpost?->formatToLog();
    }

    /**
     * 開始ログ登録
     *
     * @param string $usrUserId
     * @param string $mstAdventBattleId
     * @param int $partyNo
     * @return void
     */
    public function sendStartLog(
        string $usrUserId,
        string $mstAdventBattleId,
        int $partyNo,
        CarbonImmutable $now,
        bool $isChallengeAd,
    ): void {
        $this->logAdventBattleActionRepository->create(
            $usrUserId,
            $mstAdventBattleId,
            LogAdventBattleResult::UNDETERMINED,
            $this->getPartyUnits($usrUserId, $partyNo),
            $this->getUsedOutpost($usrUserId),
            null,
        );

        if ($isChallengeAd) {
            $this->adPlayService->adPlay(
                $usrUserId,
                ContentType::ADVENT_BATTLE->value,
                $mstAdventBattleId,
                $now,
            );
        }
    }

    /**
     * 終了ログ登録
     *
     * @param string $usrUserId
     * @param string $mstAdventBattleId
     * @param int $partyNo
     * @param AdventBattleInGameBattleLog $inGameBattleLog
     * @return void
     */
    public function sendEndLog(
        string $usrUserId,
        string $mstAdventBattleId,
        int $partyNo,
        AdventBattleInGameBattleLog $inGameBattleLog,
    ): void {
        $this->logAdventBattleActionRepository->create(
            $usrUserId,
            $mstAdventBattleId,
            LogAdventBattleResult::VICTORY,
            $this->getPartyUnits($usrUserId, $partyNo),
            $this->getUsedOutpost($usrUserId),
            $inGameBattleLog->formatToLog(),
        );
    }

    /**
     * 終了時の報酬ログを登録する
     * sendEndLog実行時はまだ報酬の配布が実行されていないので分離しています
     * @param string $usrUserId
     * @return void
     */
    public function sendEndRewardLog(string $usrUserId)
    {
        // クリア報酬ログ
        $firstClearRewards = $this->rewardDelegator->getSentRewards(AdventBattleFirstClearReward::class);
        $alwaysRewards = $this->rewardDelegator->getSentRewards(AdventBattleAlwaysClearReward::class);
        $dropRewards = $this->rewardDelegator->getSentRewards(AdventBattleDropReward::class);
        $rewards = $firstClearRewards->concat($alwaysRewards)->concat($dropRewards);
        if ($rewards->isNotEmpty()) {
            $this->logAdventBattleRewardRepository->create(
                $usrUserId,
                $rewards,
            );
        }
    }

    /**
     * 中断ログ登録
     *
     * @param string $usrUserId
     * @param int $abortType
     * @return void
     */
    public function sendAbortLog(
        string $usrUserId,
        int $abortType,
    ): void {
        $logAdventBattleResult = LogAdventBattleResult::getOrDefault($abortType);
        if ($logAdventBattleResult->isAbortType() === false) {
            $logAdventBattleResult = LogAdventBattleResult::NONE;
        }

        $usrAdventBattleSession = $this->usrAdventBattleSessionRepository->findByUsrUserId($usrUserId);

        $this->logAdventBattleActionRepository->create(
            $usrUserId,
            $usrAdventBattleSession?->getMstAdventBattleId() ?? '',
            $logAdventBattleResult,
            $this->getPartyUnits($usrUserId, $usrAdventBattleSession?->getPartyNo()),
            $this->getUsedOutpost($usrUserId),
            null,
        );
    }

    /**
     * トップ画面の報酬ログを登録する
     *
     * @param string $usrUserId
     * @return void
     */
    public function sendTopLog(string $usrUserId): void
    {
        // 報酬ログ
        $maxScoreRewards = $this->rewardDelegator->getSentRewards(AdventBattleMaxScoreReward::class);
        $totalScoreRewards = $this->rewardDelegator->getSentRewards(AdventBattleRaidTotalScoreReward::class);
        $rewards = $maxScoreRewards->concat($totalScoreRewards);
        if ($rewards->isNotEmpty()) {
            $this->logAdventBattleRewardRepository->create(
                $usrUserId,
                $rewards,
            );
        }
    }
}

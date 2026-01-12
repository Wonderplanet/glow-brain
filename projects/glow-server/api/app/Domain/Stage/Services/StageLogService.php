<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Enums\ContentType;
use App\Domain\Common\Services\AdPlayService;
use App\Domain\Common\Services\CommonInGameService;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Stage\Entities\StageInGameBattleLog;
use App\Domain\Stage\Enums\LogStageResult;
use App\Domain\Stage\Repositories\LogStageActionRepository;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use Carbon\CarbonImmutable;

class StageLogService
{
    public function __construct(
        // Repository
        private UsrStageSessionRepository $usrStageSessionRepository,
        private LogStageActionRepository $logStageActionRepository,
        // Delegator
        private PartyDelegator $partyDelegator,
        private OutpostDelegator $outpostDelegator,
        // Common
        private CommonInGameService $commonInGameService,
        private AdPlayService $adPlayService,
    ) {
    }

    /**
     * インゲームバトルログデータを生成する
     *
     * @param array<mixed> $inGameBattleLog
     */
    public function makeStageInGameBattleLogData(array $inGameBattleLog): StageInGameBattleLog
    {
        return new StageInGameBattleLog(
            $inGameBattleLog['defeatEnemyCount'] ?? 0,
            $inGameBattleLog['defeatBossEnemyCount'] ?? 0,
            $inGameBattleLog['score'] ?? 0,
            $inGameBattleLog['clearTimeMs'] ?? 99999, // デフォルト値はスピードアタック計測値の最大とする
            $this->commonInGameService->makeDiscoveredEnemyDataList(
                $inGameBattleLog['discoveredEnemies'] ?? [],
            ),
            $this->partyDelegator->makePartyStatusList(
                collect($inGameBattleLog['partyStatus'] ?? []), // @phpstan-ignore-line
            ),
        );
    }

    public function sendStartLog(
        string $usrUserId,
        string $mstStageId,
        int $partyNo,
        CarbonImmutable $now,
        bool $isChallengeAd,
        int $autoLapCount,
    ): void {
        $this->logStageActionRepository->create(
            $usrUserId,
            $mstStageId,
            LogStageResult::UNDETERMINED,
            autoLapCount: $autoLapCount
        );

        if ($isChallengeAd) {
            $this->adPlayService->adPlay(
                $usrUserId,
                ContentType::STAGE->value,
                $mstStageId,
                $now,
            );
        }
    }

    public function sendEndLog(
        string $usrUserId,
        string $mstStageId,
        int $partyNo,
        StageInGameBattleLog $stageInGameBattleLog,
        int $autoLapCount,
    ): void {
        $usrOutpost = $this->outpostDelegator->getUsedOutpost($usrUserId);

        $this->logStageActionRepository->create(
            $usrUserId,
            $mstStageId,
            LogStageResult::VICTORY,
            $usrOutpost?->getMstOutpostId() ?? '',
            $usrOutpost?->getMstArtworkId() ?? '',
            $stageInGameBattleLog->getDefeatEnemyCount(),
            $stageInGameBattleLog->getDefeatBossEnemyCount(),
            $stageInGameBattleLog->getScore(),
            $stageInGameBattleLog->getClearTimeMs(),
            $stageInGameBattleLog->getDiscoveredEnemyDataList(),
            $stageInGameBattleLog->getPartyStatusList(),
            $autoLapCount
        );
    }

    public function sendAbortLog(
        string $usrUserId,
        int $abortType,
        int $autoLapCount,
    ): void {
        $LogStageResult = LogStageResult::getOrDefault($abortType);
        if ($LogStageResult->isAbortType() === false) {
            $LogStageResult = LogStageResult::NONE;
        }

        $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);

        $this->logStageActionRepository->create(
            $usrUserId,
            $usrStageSession?->getMstStageId() ?? '',
            $LogStageResult,
            autoLapCount: $autoLapCount
        );
    }
}

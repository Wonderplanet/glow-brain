<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Services\PvpLogService;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Pvp\Services\PvpStartCheatService;
use App\Domain\Pvp\Services\PvpStartService;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Http\Responses\ResultData\PvpStartResultData;

class PvpStartUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Repository
        private readonly UsrPvpRepository $usrPvpRepository,
        private readonly MstPvpRepository $mstPvpRepository,
        private readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        // Service
        private readonly PvpLogService $pvpLogService,
        private readonly PvpStartCheatService $pvpStartCheatService,
        private readonly PvpStartService $pvpStartService,
        private readonly PvpService $pvpService,
        // Other
        private readonly Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string $sysPvpSeasonId
     * @param bool $isUseItem
     * @param string $opponentMyId
     * @param int $partyNo
     * @param array<string, mixed> $inGameBattleLogArray
     * @return PvpStartResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        string $sysPvpSeasonId,
        bool $isUseItem,
        string $opponentMyId,
        int $partyNo,
        array $inGameBattleLogArray,
    ): PvpStartResultData {
        $usrUserId = $user->id;

        $now = $this->clock->now();

        // PVPシーズン情報を取得
        $sysPvpSeason = $this->sysPvpSeasonRepository->getById($sysPvpSeasonId);

        // シーズン期間のバリデーション
        if (is_null($sysPvpSeason) || !$sysPvpSeason->isInSeason($now)) {
            throw new GameException(
                ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                "PVP season is not active. sys_pvp_season_id: {$sysPvpSeasonId}"
            );
        }

        $mstPvp = $this->mstPvpRepository->getDefaultOrTargetById($sysPvpSeason->getId(), true);

        // ユーザのPVP情報を取得
        $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId($usrUserId, $sysPvpSeasonId);
        if ($usrPvp === null) {
            // 前回シーズンの結果を考慮してtopAPIでレコード作成するので、ここでは作成禁止
            throw new GameException(
                ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                "usr_pvps is not found. sys_pvp_season_id: {$sysPvpSeasonId}"
            );
        }

        // 挑戦回数のバリデーション
        $this->pvpStartService->validateCanStart($usrPvp, $mstPvp, $isUseItem);

        // マッチング相手の情報を取得
        $selectedOpponentCandidates = $usrPvp->getSelectedOpponentCandidatesToArray();
        if (!isset($selectedOpponentCandidates[$opponentMyId])) {
            throw new GameException(
                ErrorCode::PVP_NOT_SELECTED_USER,
                "指定された対戦相手が見つかりません: {$opponentMyId}"
            );
        }

        $opponentData = $selectedOpponentCandidates[$opponentMyId];
        $opponentPvpStatus = $this->pvpService->convertJsonToOpponentPvpStatus(
            $opponentData
        );

        // 対戦相手のスコア情報を取得
        $score = $opponentData['pvpUserProfile']['score'];

        // 対戦相手のユーザIDを取得
        $opponentMyId = $opponentData['pvpUserProfile']['myId'];

        $this->pvpStartService->startPvpSession(
            $usrUserId,
            $sysPvpSeasonId,
            $partyNo,
            $opponentMyId,
            $opponentPvpStatus,
            $score,
            $now,
            $isUseItem,
        );

        $inGameBattleLog = $this->pvpLogService->makeInGameBattleLogData($inGameBattleLogArray);
        $this->pvpStartCheatService->checkCheat(
            $inGameBattleLog,
            $usrPvp,
            $sysPvpSeason,
            $now,
            $partyNo,
        );

        // Apply transaction changes
        $this->applyUserTransactionChanges();

        return new PvpStartResultData($opponentPvpStatus);
    }
}

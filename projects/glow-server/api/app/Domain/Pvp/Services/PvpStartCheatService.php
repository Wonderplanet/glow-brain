<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Pvp\Entities\PvpInGameBattleLog;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use Carbon\CarbonImmutable;

/**
 * ランクマッチ開始時のチート制御サービス
 */
class PvpStartCheatService extends PvpCheatService
{
    /**
     * チート判定を行う
     *
     * @param PvpInGameBattleLog $inGameBattleLog
     * @param UsrPvpInterface    $usrPvp
     * @param SysPvpSeasonEntity $sysPvpSeason
     * @param CarbonImmutable    $now
     * @param int                $partyNo
     * @return void
     */
    public function checkCheat(
        PvpInGameBattleLog $inGameBattleLog,
        UsrPvpInterface $usrPvp,
        SysPvpSeasonEntity $sysPvpSeason,
        CarbonImmutable $now,
        int $partyNo,
    ): void {

        $cheatContentType = CheatContentType::PVP;

        // パーティステータスがバトル前後で異なる場合はチートと判断する為、バトル前のステータス（自分と対戦相手）を保存
        $this->cheatDelegator->initBattleStatusMismatchWithOpponent(
            $usrPvp->getUsrUserId(),
            $usrPvp->getSysPvpSeasonId(),
            $cheatContentType->value,
            $inGameBattleLog->getPartyStatus(),
            $inGameBattleLog->getArtworkPartyStatus(),
            $inGameBattleLog->getOpponentPartyStatus(),
            $inGameBattleLog->getOpponentArtworkPartyStatus(),
        );

        // 誤検出が多いので無効化
        // パーティステータスのパラメータ等が正規のものと異なる場合はチートと判断する
        // $this->handleCheatCheck(
        //     $usrPvp,
        //     $sysPvpSeason,
        //     $cheatContentType,
        //     $now,
        //     [$this->cheatDelegator, 'checkMasterDataStatusMismatch'],
        //     $inGameBattleLog->getPartyStatus(),
        //     $partyNo,
        //     '',
        // );
    }
}

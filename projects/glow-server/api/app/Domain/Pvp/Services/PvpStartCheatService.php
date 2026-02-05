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
     * @param PvpInGameBattleLog $inGameBattleLogData
     * @param UsrPvpInterface    $usrPvp
     * @param SysPvpSeasonEntity $sysPvpSeason
     * @param CarbonImmutable    $now
     * @param int                $partyNo
     * @return void
     */
    public function checkCheat(
        PvpInGameBattleLog $inGameBattleLogData,
        UsrPvpInterface $usrPvp,
        SysPvpSeasonEntity $sysPvpSeason,
        CarbonImmutable $now,
        int $partyNo,
    ): void {

        $cheatContentType = CheatContentType::PVP;

        // パーティステータスがバトル前後で異なる場合はチートと判断する為、バトル前のステータスを保存
        $this->cheatDelegator->initBattleStatusMismatch(
            $usrPvp->getUsrUserId(),
            $usrPvp->getSysPvpSeasonId(),
            $cheatContentType->value,
            $inGameBattleLogData->getPartyStatus(),
            $inGameBattleLogData->getArtworkPartyStatus(),
        );

        // 誤検出が多いので無効化
        // パーティステータスのパラメータ等が正規のものと異なる場合はチートと判断する
        // $this->handleCheatCheck(
        //     $usrPvp,
        //     $sysPvpSeason,
        //     $cheatContentType,
        //     $now,
        //     [$this->cheatDelegator, 'checkMasterDataStatusMismatch'],
        //     $inGameBattleLogData->getPartyStatus(),
        //     $partyNo,
        //     '',
        // );
    }
}

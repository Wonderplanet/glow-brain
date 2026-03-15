<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Pvp\Entities\PvpInGameBattleLog;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PvpEndCheatService extends PvpCheatService
{
    /**
     */
    public function checkCheat(
        PvpInGameBattleLog $inGameBattleLog,
        UsrPvpInterface $usrPvp,
        SysPvpSeasonEntity $sysPvpSeason,
        CarbonImmutable $now,
        int $partyNo,
        int $battleTimeSeconds,
        Collection $opponentPvpUnits,
        Collection $opponentMstUnitEncyclopediaEffectIds,
    ): void {
        $cheatContentType = CheatContentType::PVP;

        // バトル時間が下限の想定以下の場合はチートと判断する
        $this->handleCheatCheck(
            $usrPvp,
            $sysPvpSeason,
            $cheatContentType,
            $now,
            [$this->cheatDelegator, 'checkBattleTime'],
            $battleTimeSeconds
        );

        // 最大ダメージが上限の想定以上の場合はチートと判断する
        $this->handleCheatCheck(
            $usrPvp,
            $sysPvpSeason,
            $cheatContentType,
            $now,
            [$this->cheatDelegator, 'checkMaxDamage'],
            $inGameBattleLog->getMaxDamage()
        );

        // パーティステータスがバトル前後で異なる場合はチートと判断する（自分と対戦相手の両方をチェック）
        $this->handleCheatCheck(
            $usrPvp,
            $sysPvpSeason,
            $cheatContentType,
            $now,
            [$this->cheatDelegator, 'checkBattleStatusMismatchWithOpponent'],
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

        // 誤検出が多いので無効化
        // パーティステータスのパラメータ等が正規のものと異なる場合はチートと判断する
        // $this->handleCheatCheck(
        //     $usrPvp,
        //     $sysPvpSeason,
        //     $cheatContentType,
        //     $now,
        //     [$this->cheatDelegator, 'checkOpponentMasterDataStatusMismatch'],
        //     $inGameBattleLog->getOpponentPartyStatus(),
        //     $opponentPvpUnits->map(fn(PvpUnitData $pvpUnit) => $pvpUnit->toCheatCheckUnit()),
        //     $opponentMstUnitEncyclopediaEffectIds,
        // );
    }
}

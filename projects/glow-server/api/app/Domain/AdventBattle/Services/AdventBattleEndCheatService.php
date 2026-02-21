<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Cheat\Enums\CheatContentType;
use Carbon\CarbonImmutable;

/**
 * 降臨バトル終了時のチート制御サービス
 */
class AdventBattleEndCheatService extends AdventBattleCheatService
{
    /**
     * チート判定を行う
     *
     * @param AdventBattleInGameBattleLog $inGameBattleLogData
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param CarbonImmutable $now
     * @param int $partyNo
     * @param int $battleTimeSeconds
     * @param string $eventBonusGroupId
     */
    public function checkCheat(
        AdventBattleInGameBattleLog $inGameBattleLogData,
        UsrAdventBattleInterface $usrAdventBattle,
        CarbonImmutable $now,
        int $partyNo,
        int $battleTimeSeconds,
        string $eventBonusGroupId,
    ): void {

        $cheatContentType = CheatContentType::ADVENT_BATTLE;

        // バトル時間が下限の想定以下の場合はチートと判断する
        $this->handleCheatCheck(
            $usrAdventBattle,
            $cheatContentType,
            $now,
            [$this->cheatDelegator, 'checkBattleTime'],
            $battleTimeSeconds
        );

        // 最大ダメージが上限の想定以上の場合はチートと判断する
        $this->handleCheatCheck(
            $usrAdventBattle,
            $cheatContentType,
            $now,
            [$this->cheatDelegator, 'checkMaxDamage'],
            $inGameBattleLogData->getMaxDamage()
        );

        // パーティステータスがバトル前後で異なる場合はチートと判断する
        $this->handleCheatCheck(
            $usrAdventBattle,
            $cheatContentType,
            $now,
            [$this->cheatDelegator, 'checkBattleStatusMismatch'],
            $inGameBattleLogData->getPartyStatus(),
            $inGameBattleLogData->getArtworkPartyStatus(),
        );

        // 誤検出が多いので無効化
        // パーティステータスのパラメータ等が正規のものと異なる場合はチートと判断する
        // $this->handleCheatCheck(
        //     $usrAdventBattle,
        //     $cheatContentType,
        //     $now,
        //     [$this->cheatDelegator, 'checkMasterDataStatusMismatch'],
        //     $inGameBattleLogData->getPartyStatus(),
        //     $partyNo,
        //     $eventBonusGroupId,
        // );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Cheat\Enums\CheatContentType;
use Carbon\CarbonImmutable;

/**
 * 降臨バトル開始時のチート制御サービス
 */
class AdventBattleStartCheatService extends AdventBattleCheatService
{
    /**
     * チート判定を行う
     *
     * @param AdventBattleInGameBattleLog $inGameBattleLogData
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param CarbonImmutable $now
     * @param int $partyNo
     * @param string $eventBonusGroupId
     * @return void
     */
    public function checkCheat(
        AdventBattleInGameBattleLog $inGameBattleLogData,
        UsrAdventBattleInterface $usrAdventBattle,
        CarbonImmutable $now,
        int $partyNo,
        string $eventBonusGroupId,
    ): void {

        $cheatContentType = CheatContentType::ADVENT_BATTLE;

        // パーティステータスがバトル前後で異なる場合はチートと判断する為、バトル前のステータスを保存
        $this->cheatDelegator->initBattleStatusMismatch(
            $usrAdventBattle->getUsrUserId(),
            $usrAdventBattle->getMstAdventBattleId(),
            CheatContentType::ADVENT_BATTLE->value,
            $inGameBattleLogData->getPartyStatus(),
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

<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Repositories;

use App\Domain\AdventBattle\Enums\LogAdventBattleResult;
use App\Domain\AdventBattle\Models\LogAdventBattleAction;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogAdventBattleActionRepository extends LogModelRepository
{
    protected string $modelClass = LogAdventBattleAction::class;

    /**
     * @param array<mixed>|null $partyUnits
     * @param array<mixed>|null $usedOutpost
     * @param array<mixed>|null $inGameBattleLog
     */
    public function create(
        string $usrUserId,
        string $mstAdventBattleId,
        LogAdventBattleResult $result,
        ?array $partyUnits,
        ?array $usedOutpost,
        ?array $inGameBattleLog,
    ): void {
        $apiPath = request()->path();

        // 降臨バトルAPI以外はログを保存しない
        if (strpos($apiPath, 'api/advent_battle/') !== 0) {
            return;
        }

        $model = new LogAdventBattleAction();

        $model->setUsrUserId($usrUserId);
        $model->setMstAdventBattleId($mstAdventBattleId);
        $model->setResult($result);
        $model->setApiPath($apiPath);
        $model->setPartyUnits($partyUnits);
        $model->setUsedOutpost($usedOutpost);
        $model->setInGameBattleLog($inGameBattleLog);

        $this->addModel($model);
    }
}

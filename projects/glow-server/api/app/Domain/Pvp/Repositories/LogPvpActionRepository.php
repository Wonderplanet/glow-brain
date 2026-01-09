<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Pvp\Enums\LogPvpResult;
use App\Domain\Pvp\Models\LogPvpAction;
use App\Domain\Resource\Log\Repositories\LogModelRepository;

class LogPvpActionRepository extends LogModelRepository
{
    protected string $modelClass = LogPvpAction::class;

    /**
     * @param array<mixed>|null $inGameBattleLog
     */
    public function create(
        string $usrUserId,
        string $sysPvpSeasonId,
        LogPvpResult $result,
        string $myPvpStatus,
        string $opponentMyId,
        string $opponentPvpStatus,
        ?array $inGameBattleLog,
    ): void {
        $apiPath = request()->path();

        // ランクマッチAPI以外はログを保存しない
        if (strpos($apiPath, 'api/pvp/') !== 0) {
            return;
        }

        $model = new LogPvpAction();
        $model->setUsrUserId($usrUserId);
        $model->setSysPvpSeasonId($sysPvpSeasonId);
        $model->setResult($result);
        $model->setApiPath($apiPath);
        $model->setMyPvpStatus($myPvpStatus);
        $model->setOpponentMyId($opponentMyId);
        $model->setOpponentPvpStatus($opponentPvpStatus);
        $model->setInGameBattleLog($inGameBattleLog);

        $this->addModel($model);
    }
}

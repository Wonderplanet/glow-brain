<?php

declare(strict_types=1);

namespace App\Domain\Stage\Repositories;

use App\Domain\Resource\Entities\InGameDiscoveredEnemy;
use App\Domain\Resource\Entities\PartyStatus;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Stage\Enums\LogStageResult;
use App\Domain\Stage\Models\LogStageAction;
use Illuminate\Support\Collection;

class LogStageActionRepository extends LogModelRepository
{
    protected string $modelClass = LogStageAction::class;

    /**
     * @param Collection<InGameDiscoveredEnemy> $discoveredEnemies
     */
    public function create(
        string $usrUserId,
        string $mstStageId,
        LogStageResult $result,
        string $mstOutpostId = '',
        string $mstArtworkId = '',
        int $defeatEnemyCount = 0,
        int $defeatBossEnemyCount = 0,
        int $score = 0,
        ?int $clearTimeMs = null,
        ?Collection $discoveredEnemies = null,
        ?Collection $partyStatus = null,
        int $autoLapCount = 1,
    ): void {
        $apiPath = request()->path();

        // ステージAPI以外はログを保存しない
        if (strpos($apiPath, 'api/stage/') !== 0) {
            return;
        }

        $model = new LogStageAction();

        $model->setUsrUserId($usrUserId);
        $model->setMstStageId($mstStageId);
        $model->setResult($result);
        $model->setApiPath($apiPath);
        $model->setMstOutpostId($mstOutpostId);
        $model->setMstArtworkId($mstArtworkId);
        $model->setDefeatEnemyCount($defeatEnemyCount);
        $model->setDefeatBossEnemyCount($defeatBossEnemyCount);
        $model->setScore($score);
        $model->setClearTimeMs($clearTimeMs);
        $discoveredEnemies = $discoveredEnemies ?? collect();
        $model->setDiscoveredEnemies(
            $discoveredEnemies->map(
                fn (InGameDiscoveredEnemy $enemy) => $enemy->formatToLog(),
            )->all(),
        );
        $partyStatus = $partyStatus ?? collect();
        $model->setPartyStatus(
            $partyStatus->map(
                fn (PartyStatus $status) => $status->formatToLog(),
            )->all(),
        );
        $model->setAutoLapCount($autoLapCount);

        $this->addModel($model);
    }
}

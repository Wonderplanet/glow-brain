<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Stage\Enums\LogStageResult;

/**
 * @property string $mst_stage_id
 * @property string $api_path
 * @property int $result
 * @property string $mst_outpost_id
 * @property string $mst_artwork_id
 * @property int $defeat_enemy_count
 * @property int $defeat_boss_enemy_count
 * @property int $score
 * @property int|null $clear_time_ms
 * @property string $discovered_enemies
 * @property string $party_status
 * @property int|null $auto_lap_count
 */
class LogStageAction extends LogModel
{
    use HasFactory;

    public function setMstStageId(string $mstStageId): void
    {
        $this->mst_stage_id = $mstStageId;
    }

    public function setApiPath(string $apiPath): void
    {
        $this->api_path = $apiPath;
    }

    public function setResult(LogStageResult $result): void
    {
        $this->result = $result->value;
    }

    public function setMstOutpostId(string $mstOutpostId): void
    {
        $this->mst_outpost_id = $mstOutpostId;
    }

    public function setMstArtworkId(string $mstArtworkId): void
    {
        $this->mst_artwork_id = $mstArtworkId;
    }

    public function setDefeatEnemyCount(int $defeatEnemyCount): void
    {
        $this->defeat_enemy_count = $defeatEnemyCount;
    }

    public function setDefeatBossEnemyCount(int $defeatBossEnemyCount): void
    {
        $this->defeat_boss_enemy_count = $defeatBossEnemyCount;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function setClearTimeMs(?int $clearTimeMs): void
    {
        $this->clear_time_ms = $clearTimeMs;
    }

    /**
     * @param array<mixed> $discoveredEnemies
     */
    public function setDiscoveredEnemies(array $discoveredEnemies): void
    {
        $this->discovered_enemies = json_encode($discoveredEnemies);
    }

    /**
     * @param array<mixed> $partyStatus
     */
    public function setPartyStatus(array $partyStatus): void
    {
        $this->party_status = json_encode($partyStatus);
    }

    public function setAutoLapCount(int $autoLapCount): void
    {
        $this->auto_lap_count = $autoLapCount;
    }
}

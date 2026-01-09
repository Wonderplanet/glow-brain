<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Entities;

use App\Domain\Resource\Entities\InGameBattleLog;
use App\Domain\Resource\Entities\InGameDiscoveredEnemy;
use Illuminate\Support\Collection;

class AdventBattleInGameBattleLog extends InGameBattleLog
{
    /**
     * @param int $defeatEnemyCount
     * @param int $defeatBossEnemyCount
     * @param int $score
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatus
     * @param int $maxDamage
     * @param Collection<\App\Domain\Resource\Entities\InGameDiscoveredEnemy> $discoveredEnemyDataList
     */
    public function __construct(
        private readonly int $defeatEnemyCount,
        private readonly int $defeatBossEnemyCount,
        private readonly int $score,
        private readonly Collection $partyStatus,
        private readonly int $maxDamage,
        private Collection $discoveredEnemyDataList,
    ) {
    }

    public function getDefeatEnemyCount(): int
    {
        return $this->defeatEnemyCount;
    }

    public function getDefeatBossEnemyCount(): int
    {
        return $this->defeatBossEnemyCount;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getPartyStatus(): Collection
    {
        return $this->partyStatus;
    }

    public function getMaxDamage(): int
    {
        return $this->maxDamage;
    }

    /**
     * @return Collection<\App\Domain\Resource\Entities\InGameDiscoveredEnemy>
     */
    public function getDiscoveredEnemyDataList(): Collection
    {
        return $this->discoveredEnemyDataList;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'defeat_enemy_count' => $this->defeatEnemyCount,
            'defeat_boss_enemy_count' => $this->defeatBossEnemyCount,
            'score' => $this->score,
            'party_status' => $this->partyStatus->map(fn($status) => $status->formatToLog())->toArray(),
            'max_damage' => $this->maxDamage,
            'discovered_enemies' => $this->discoveredEnemyDataList->map(
                fn(InGameDiscoveredEnemy $discoveredEnemyData) => $discoveredEnemyData->formatToLog()
            )->toArray(),
        ];
    }
}

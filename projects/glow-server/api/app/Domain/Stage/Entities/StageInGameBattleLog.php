<?php

declare(strict_types=1);

namespace App\Domain\Stage\Entities;

use App\Domain\Resource\Entities\InGameBattleLog;
use App\Domain\Resource\Entities\InGameDiscoveredEnemy;
use App\Domain\Resource\Entities\PartyStatus;
use Illuminate\Support\Collection;

class StageInGameBattleLog extends InGameBattleLog
{
    /**
     * @param int $defeatEnemyCount
     * @param int $defeatBossEnemyCount
     * @param int $score
     * @param int $clearTimeMs
     * @param Collection<\App\Domain\Resource\Entities\InGameDiscoveredEnemy> $discoveredEnemyDataList
     * @param Collection<\App\Domain\Resource\Entities\PartyStatus> $partyStatusList
     * @param Collection<\App\Domain\Resource\Entities\ArtworkPartyStatus> $artworkPartyStatusList
     */
    public function __construct(
        private int $defeatEnemyCount,
        private int $defeatBossEnemyCount,
        private int $score,
        private int $clearTimeMs,
        private Collection $discoveredEnemyDataList,
        private Collection $partyStatusList,
        private Collection $artworkPartyStatusList,
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

    public function getClearTimeMs(): int
    {
        return $this->clearTimeMs;
    }

    /**
     * @return Collection<\App\Domain\Resource\Entities\InGameDiscoveredEnemy>
     */
    public function getDiscoveredEnemyDataList(): Collection
    {
        return $this->discoveredEnemyDataList;
    }

    /**
     * @return Collection<\App\Domain\Resource\Entities\PartyStatus>
     */
    public function getPartyStatusList(): Collection
    {
        return $this->partyStatusList;
    }

    /**
     * @return Collection<\App\Domain\Resource\Entities\ArtworkPartyStatus>
     */
    public function getArtworkPartyStatusList(): Collection
    {
        return $this->artworkPartyStatusList;
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
            'clear_time_ms' => $this->clearTimeMs,
            'discovered_enemies' => $this->discoveredEnemyDataList->map(
                fn(InGameDiscoveredEnemy $discoveredEnemyData) => $discoveredEnemyData->formatToLog()
            )->toArray(),
            'party_status' => $this->partyStatusList->map(
                fn(PartyStatus $partyStatus) => $partyStatus->formatToLog()
            )->toArray(),
            'artwork_party_status' => $this->artworkPartyStatusList->map(
                fn($status) => $status->formatToLog()
            )->toArray(),
        ];
    }
}

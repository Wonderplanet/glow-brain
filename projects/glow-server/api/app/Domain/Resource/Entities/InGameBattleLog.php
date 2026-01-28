<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use Illuminate\Support\Collection;

abstract class InGameBattleLog
{
    public function getDefeatEnemyCount(): int
    {
        return 0;
    }

    public function getDefeatBossEnemyCount(): int
    {
        return 0;
    }

    public function getScore(): int
    {
        return 0;
    }

    public function getClearTimeMs(): int
    {
        return 0;
    }

    public function getPartyStatus(): Collection
    {
        return collect();
    }

    public function getMaxDamage(): int
    {
        return 0;
    }

    public function getDiscoveredEnemyDataList(): Collection
    {
        return collect();
    }

    /**
     * @return array<mixed>
     */
    abstract public function formatToLog(): array;
}

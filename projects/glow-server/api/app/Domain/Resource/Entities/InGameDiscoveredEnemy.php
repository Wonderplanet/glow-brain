<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

/**
 * インゲーム中に発見した敵キャラ情報を格納するデータクラス
 */
class InGameDiscoveredEnemy
{
    public function __construct(
        private string $mstEnemyCharacterId,
        private int $count,
    ) {
    }

    public function getMstEnemyCharacterId(): string
    {
        return $this->mstEnemyCharacterId;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'mst_enemy_character_id' => $this->mstEnemyCharacterId,
            'count' => $this->count,
        ];
    }
}

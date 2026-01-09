<?php

declare(strict_types=1);

namespace App\Domain\InGame\Entities;

use App\Domain\Resource\Mst\Entities\MstEnemyCharacterEntity;

/**
 * インゲーム中で発見した敵情報を表すエンティティ
 */
class DiscoveredEnemy
{
    /** @var string 発見した敵キャラID(mst_enemy_characters.id) */
    private string $mstEnemyCharacterId;
    /** @var string 発見した敵キャラの作品ID(mst_series.id) */
    private string $mstSeriesId;
    /** @var int 発見した体数 */
    private int $discoveredCount;
    /** @var bool 新発見かどうか true: 新発見, false: 既知 */
    private bool $isNew;

    public function __construct(
        MstEnemyCharacterEntity $mstEnemyCharacter,
        int $discoveredCount,
        bool $isNew,
    ) {
        $this->mstEnemyCharacterId = $mstEnemyCharacter->getId();
        $this->mstSeriesId = $mstEnemyCharacter->getMstSeriesId();
        $this->discoveredCount = $discoveredCount;
        $this->isNew = $isNew;
    }

    public function getMstEnemyCharacterId(): string
    {
        return $this->mstEnemyCharacterId;
    }

    public function getMstSeriesId(): string
    {
        return $this->mstSeriesId;
    }

    public function getDiscoveredCount(): int
    {
        return $this->discoveredCount;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }
}

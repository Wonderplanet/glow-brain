<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

/**
 * 図鑑新着バッチ消失時のリワード獲得できるタイプ
 */
enum EncyclopediaType: string
{
    case ARTWORK = 'Artwork';
    case UNIT = 'Unit';
    case ENEMY_DISCOVERY = 'EnemyDiscovery';
    case EMBLEM = 'Emblem';
}

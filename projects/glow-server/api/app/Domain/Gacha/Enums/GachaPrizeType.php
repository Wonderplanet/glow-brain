<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Enums;

enum GachaPrizeType: string
{
    // 通常枠
    case REGULAR = 'Regular';
    // 10連確定枠
    case FIXED = 'Fixed';
    // 最高レアリティ天井
    case MAX_RARITY = 'MaxRarity';
    // ピックアップ天井
    case PICKUP = 'Pickup';
}

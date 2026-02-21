<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Enums;

/**
 * BOXガチャで設定可能な報酬タイプ
 */
enum BoxGachaRewardType: string
{
    case ITEM = 'Item';
    case ARTWORK = 'Artwork';
    case FREE_DIAMOND = 'FreeDiamond';
    case COIN = 'Coin';
    case UNIT = 'Unit';
    case EMBLEM = 'Emblem';
}

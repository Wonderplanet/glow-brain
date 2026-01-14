<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Enums;

enum UpperType: string
{
    case MAX_RARITY = 'MaxRarity';
    case PICKUP = 'Pickup';
}

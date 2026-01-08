<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Gacha\Enums\GachaPrizeType;

enum GachaSetName: string
{
    case REGULAR = "通常";
    case FIXED = "10連確定";
    case MAX_RARITY = "最高レアリティ確定";
    case PICKUP = "ピックアップ確定";

    public static function getLabelfromGachaPrizeType(string $prizeType): string
    {
        return match ($prizeType) {
            GachaPrizeType::REGULAR->value => self::REGULAR->value,
            GachaPrizeType::FIXED->value => self::FIXED->value,
            GachaPrizeType::MAX_RARITY->value => self::MAX_RARITY->value,
            GachaPrizeType::PICKUP->value => self::PICKUP->value,
            default => "不明",
        };
    }
}

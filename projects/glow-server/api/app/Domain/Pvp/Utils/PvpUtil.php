<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Utils;

class PvpUtil
{
    public static function makeRankClassLevelKey(
        string $pvpRankClassTypeValue,
        int $pvpRankClassLevel,
    ): string {
        // $pvpRankClassTypeValue$pvpRankClassLevelの形で結合する
        return sprintf(
            '%s%d',
            $pvpRankClassTypeValue,
            $pvpRankClassLevel,
        );
    }
}

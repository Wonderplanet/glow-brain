<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Enums;

/**
 * BOXガチャのアクションタイプ（ログ用）
 */
enum BoxGachaActionType: string
{
    case DRAW = 'Draw';
    case RESET = 'Reset';
}

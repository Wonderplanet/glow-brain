<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum StageRuleType: string
{
    // ヒーローゲートのHPがNで開始
    case OUTPOST_HP = 'OutpostHp';
    // コンティニュー不可
    case NO_CONTINUE = 'NoContinue';
}

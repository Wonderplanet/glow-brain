<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Enums;

enum TutorialFunctionName: string
{
    // チュートリアルガシャの引き直しを終了してガシャ結果を確定させた
    case GACHA_CONFIRMED = 'GachaConfirmed';

    // メインパート完了
    case MAIN_PART_COMPLETED = 'MainPartCompleted';
}

<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Enums;

enum TutorialFunctionName: string
{
    // 旧チュートリアルガシャの引き直しを終了してガシャ結果を確定させた（旧チュートリアル用、既存データ判定用として残す）
    case GACHA_CONFIRMED = 'GachaConfirmed';

    // 新チュートリアルガシャの引き直しを終了してガシャ結果を確定させた
    case NEW_GACHA_CONFIRMED = 'NewGachaConfirmed';

    // 旧チュートリアルのメインパート完了の1つ前
    case START_MAIN_PART3 = 'StartMainPart3';

    // メインパート完了
    case MAIN_PART_COMPLETED = 'MainPartCompleted';
}

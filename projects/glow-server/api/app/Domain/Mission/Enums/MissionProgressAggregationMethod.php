<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

enum MissionProgressAggregationMethod: string
{
    // 最大：最大値を取る進捗値を採用する
    case MAX = 'Max';

    // 合計：進捗値の合計を新たな進捗値とする
    case SUM = 'Sum';

    // フラグ：進捗があれば1、なければ0とする
    case BINARY = 'Binary';

    /**
     * 同期：トリガーされた進捗値を大小関係比較することなくそのまま採用する
     *
     * 1API中に複数回トリガーされる場合は、挙動に注意する。（別メソッドを新設することを推奨）
     * 登録されたトリガーの配列内で、後方のものが採用されることになるため。
     *
     * 同期メソッド使用例: criterion_type=COMEBACK_LOGIN_COUNT
     * ミッション機構とは異なるロジックでリセットが実行される場合、ミッション機構側で現在の進捗値を算出することができない。
     * その場合、ミッショントリガー実行時の進捗値に、異なるロジックでリセットされた進捗値を指定してもらうことで、
     * トリガーされた進捗値を、現在のミッション進捗値として、そのまま採用するようにするためのメソッド
     */
    case SYNC = 'Sync';
}

<?php

declare(strict_types=1);

namespace App\Domain\Shop\Constants;

class AdjustConstant
{
    public const ADJUST_S2S_ENDPOINT = 'https://s2s.adjust.com/event';

    // 金額変換定数（100円 = 1.00の形式に変換）
    public const AMOUNT_DIVISOR = 100;

    // デフォルト広告ID（広告IDが取得できない場合に使用）
    public const DEFAULT_AD_ID = '00000000-0000-0000-0000-000000000000';

    // Adjust Status定数
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped'; // 無料商品などでスキップ
}

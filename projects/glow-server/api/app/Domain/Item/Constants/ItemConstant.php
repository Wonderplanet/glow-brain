<?php

declare(strict_types=1);

namespace App\Domain\Item\Constants;

class ItemConstant
{
    /** @var int 獲得済みユニット1体に対するユニットピースの交換比率 */
    public const UNIT_PIECE_EXCHANGE_RATE = 1;

    /** @var int 1回あたりのランダムかけらボックスの最大交換数 */
    public const FRAGMENT_BOX_MAX_EXCHANGE = 100;

    public const MAX_POSESSION_ITEM_COUNT = 999999999;
}

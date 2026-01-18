<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * バッチから無償一次通貨を付与したログの記録をする際のトリガー
 */
class AddFreeCurrencyBatchTrigger extends Trigger
{
    /**
     * コンストラクタ
     */
    public function __construct(string $comment)
    {
        $triggerType = Trigger::TRIGGER_TYPE_ADD_CURRENCY_FREE_BATCH;

        // 特に記載する内容がないため空白
        $triggerId = '';
        $triggerName = '';

        // コメントをJSON形式でdetailに記載する
        $detail = $comment;

        parent::__construct($triggerType, $triggerId, $triggerName, $detail);
    }
}

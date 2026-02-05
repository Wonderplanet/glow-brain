<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * バッチから有償一次通貨を回収したログの記録をする際のトリガー
 */
class CollectPaidCurrencyBatchTrigger extends Trigger
{
    /**
     * コンストラクタ
     */
    public function __construct(string $triggerId, string $comment)
    {
        $triggerType = Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH;

        // 特に記載する内容がないため空白
        $triggerName = '';

        // コメントをJSON形式でdetailに記載する
        $detail = $comment;

        // triggerIdには回収したusrStoreProductHistoryのidを受け取っている
        parent::__construct($triggerType, $triggerId, $triggerName, $detail);
    }
}

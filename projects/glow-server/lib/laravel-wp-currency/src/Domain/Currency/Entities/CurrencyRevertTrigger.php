<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

/**
 * 一次通貨返却時のログの記録をする際のトリガー
 */
class CurrencyRevertTrigger extends Trigger
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $triggerType = Trigger::TRIGGER_TYPE_REVERT_CURRENCY;

        // 特に記載する内容がないため空白
        $triggerId = '';
        $triggerName = '';
        $detail = '';

        parent::__construct($triggerType, $triggerId, $triggerName, $detail);
    }
}

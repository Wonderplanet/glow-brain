<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * クイック探索報酬受け取り時に指定するトリガー
 */
class IdleIncentiveQuickReceiveTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'idle_incentive_quick_receive';

    /**
     * @param int $cost 消費する通貨量
     */
    public function __construct(
        int $cost,
    ) {
        parent::__construct('', '', ['cost' => $cost]);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * ステージをコンテニューした際に指定するトリガー
 */
class StageContinueTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'stage_continue';

    /**
     * @param string $mstStageId
     * @param int $cost
     */
    public function __construct(
        string $mstStageId,
        int $cost,
    ) {
        parent::__construct('', '', ['mstStageId' => $mstStageId, 'cost' => $cost]);
    }
}

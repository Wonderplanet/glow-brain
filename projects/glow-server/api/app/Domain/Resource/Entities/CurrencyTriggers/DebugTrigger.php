<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * Debug用Triggerクラス
 */
class DebugTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'debug';

    /**
     * @param string $triggerName デバッグコマンドの名前
     */
    public function __construct(
        string $triggerName
    ) {
        parent::__construct('', $triggerName, []);
    }
}

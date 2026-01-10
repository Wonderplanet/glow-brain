<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * スタミナ購入した際に指定するトリガー
 */
class BuyStaminaTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'consume_stamina';

    /**
     *
     * @param int $requiredDiamondAmount 購入時のダイヤの消費量
     */
    public function __construct(
        int $requiredDiamondAmount,
    ) {
        parent::__construct('', '', ['requiredDiamondAmount' => $requiredDiamondAmount]);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * ショップパック購入時に指定するトリガー
 */
class TradeShopPackTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'trade_shop_pack';

    /**
     * @param string $mstShopPackId ショップパックID
     * @param int $cost 消費する通貨量
     */
    public function __construct(
        string $mstShopPackId,
        int $cost,
    ) {
        parent::__construct($mstShopPackId, '', ['cost' => $cost]);
    }
}

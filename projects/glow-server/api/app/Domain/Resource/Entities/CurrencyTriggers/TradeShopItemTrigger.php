<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * ショップアイテム購入時に指定するトリガー
 */
class TradeShopItemTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'trade_shop_item';

    /**
     * @param string $mstShopItemId ショップアイテムID
     * @param int $cost 消費する通貨量
     */
    public function __construct(
        string $mstShopItemId,
        int $cost,
    ) {
        parent::__construct($mstShopItemId, '', ['cost' => $cost]);
    }
}

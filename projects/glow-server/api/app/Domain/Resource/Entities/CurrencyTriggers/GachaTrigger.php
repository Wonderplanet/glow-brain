<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * ガチャを実行した際に指定するトリガー
 */
class GachaTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'gacha';

    /**
     *
     * @param string $gachaId
     * @param string $gachaName
     */
    public function __construct(
        string $gachaId,
        string $gachaName,
    ) {
        parent::__construct($gachaId, $gachaName);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

/**
 * アイテム交換所での報酬
 */
class ItemTradeReward extends BaseReward
{
    /**
     * @param string $type 獲得した報酬のリソースタイプ
     * @param ?string $resourceId 獲得した報酬のリソースID
     * @param int $amount 獲得した報酬の量
     * @param LogResourceTriggerSource $triggerSource 獲得経緯のソース。機能コードを指定する。
     * @param string $consumeMstItemId 消費したアイテムのマスタID
     */
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        LogResourceTriggerSource $triggerSource,
        string $consumeMstItemId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                $triggerSource->value,
                $consumeMstItemId,
            ),
        );
    }
}

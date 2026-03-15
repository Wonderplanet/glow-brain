<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\RewardType;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem as BaseMstIdleIncentiveItem;
use App\Dtos\RewardDto;

class MstIdleIncentiveItem extends BaseMstIdleIncentiveItem
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_item()
    {
        return $this->hasOne(MstItem::class, 'id', 'mst_item_id');
    }

    public function isCharacterFragment(): bool
    {
        return $this->type === ItemType::CHARACTER_FRAGMENT->value;
    }

    /**
     * @param ?int $amount 経過時間で報酬量の調整を行う際にamountを指定して呼び出せるように用意した引数
     */
    public function getRewardAttribute(?int $amount = null)
    {
        if ($amount === null) {
            $amount = $this->base_amount;
        }

        return new RewardDto(
            $this->id,
            RewardType::ITEM->value,
            $this->mst_item_id,
            $amount,
        );
    }
}

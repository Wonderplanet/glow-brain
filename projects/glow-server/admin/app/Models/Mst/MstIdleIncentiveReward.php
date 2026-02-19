<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward as BaseMstIdleIncentiveReward;

class MstIdleIncentiveReward extends BaseMstIdleIncentiveReward
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_stage()
    {
        return $this->hasOne(MstStage::class, 'id', 'mst_stage_id');
    }

    public function mst_idle_incentive_item()
    {
        return $this->hasMany(MstIdleIncentiveItem::class, 'mst_idle_incentive_item_group_id', 'mst_idle_incentive_item_group_id');
    }
}
